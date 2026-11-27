<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Support\TypingAttempt;
use App\Support\TypingPractice;
use App\Support\TypingQuestions;
use App\Support\TypingScore;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OverflowException;

// プレイトークンと測定値を検証し、成績をサーバーで採点して保存する。
class TypingResultStoreController extends Controller
{
    public function __invoke(Request $request, TypingAttempt $attempts, TypingQuestions $questions, TypingPractice $practice, TypingScore $scoring): JsonResponse
    {
        $data = $request->validate([
            'attempt' => ['required', 'string', 'max:2048'],
            'elapsedMs' => ['required', function ($attribute, $value, $fail) {
                if ((!is_int($value) && !is_float($value)) || !is_finite($value)) {
                    $fail('経過時間は有限の数値で指定してください。');
                }
            }],
        ]);
        $userId = (int) $request->user()->getAuthIdentifier();
        $attempt = $attempts->read($data['attempt'], $userId);
        // 問題セットとモードはトークンを基準にする。ゲームのみブラウザで計測した正打数・ミス数を受け取る。
        if ($attempt['mode'] === 'game') {
            $data += $request->validate([
                'correct' => ['required', 'integer:strict', 'min:0', 'max:9007199254740991'],
                'miss' => ['required', 'integer:strict', 'min:0', 'max:9007199254740991'],
            ]);
        }
        $level = TypingQuestions::LEVELS[$attempt['mode']];
        $type = TypingQuestions::TYPES[$attempt['type']];
        $db = (new Result)->getConnection();

        [$result, $rank, $counts] = $db->transaction(function () use ($db, $attempt, $data, $userId, $level, $type, $questions, $practice, $scoring, $request) {
            // 既存のキャッシュテーブルの一意キーを使い、成績と保存時の順位を同じトランザクションで確定する。
            $receipts = $db->table(config('cache.stores.database.table', 'cache'));
            $prefix = 'typing-result-receipt:';
            $key = $prefix.$attempt['id'];
            $claimed = (clone $receipts)->insertOrIgnore(['key' => $key, 'expiration' => $attempt['expires'] + 86400, 'value' => '']);
            if (!$claimed) {
                $receipt = json_decode((string) (clone $receipts)->where('key', $key)->value('value'), true);
                // 以前の保存済み記録には成績 ID だけがある。
                $resultId = is_array($receipt) ? $receipt['resultId'] : $receipt;
                $result = Result::query()->where('user_id', $userId)->where('question_id', $attempt['setId'])->findOrFail($resultId);

                return [$result, $receipt['rank'] ?? null, $receipt['counts'] ?? null];
            }

            // 初回保存では、対象セットが現在も公開・出題可能かをDBで確認する。
            $set = $questions->published()->where('level', $level)->where('type', $type)->findOrFail($attempt['setId']);
            $items = $questions->playItems($set);
            abort_if($items === null, 422, 'この問題セットは現在出題できません。');
            $counts = null;
            if ($attempt['mode'] === 'practice') {
                abort_unless($attempt['revision'] === hash('sha256', json_encode($items, JSON_THROW_ON_ERROR)), 422, '問題セットが変更されています。');
                // 文字列として受け取り、Laravelの入力整形で回答の前後の空白が消えないようにする。
                $input = $request->validate(['answers' => ['required', 'string', 'json']]);
                $answers = json_decode($input['answers']);
                abort_unless(is_array($answers) && count($answers) === count($items), 422, '回答数が一致しません。');
                foreach ($answers as $answer) {
                    abort_unless($practice->accepts($answer), 422, '回答の形式または長さが無効です。');
                }
                // 実践はDBの問題本文と提出順の回答から件数を再計算し、送信された集計値には依存しない。
                $counts = $practice->total($items, $answers);
                $data['correct'] = $counts['correct'];
                $data['miss'] = $counts['typo'] + $counts['omission'] + $counts['extra'];
            }
            $elapsedMs = $data['elapsedMs'];
            $seconds = floor(max(0, $elapsedMs) / 1000);
            abort_if($seconds >= -(float) PHP_INT_MIN, 422, '経過時間を整数秒で保存できません。');
            try {
                $score = $scoring->calculate($attempt['mode'], $attempt['type'], $data['correct'], $data['miss'], $elapsedMs);
            } catch (OverflowException) {
                abort(422, 'スコアを整数で保存できません。');
            }

            $result = new Result;
            $result->user_id = $userId;
            $result->question_id = $set->id;
            $result->score = $score;
            $result->correct = $data['correct'];
            $result->miss = $data['miss'];
            $result->input_time = (int) $seconds;
            $result->save();

            try {
                // 同一セットで今回より高いスコアの件数に1を足し、同点を同順位にする。
                // 順位クエリの失敗時は内側のセーブポイントだけを戻し、成績は保存する。
                $rank = $db->transaction(fn () => Result::query()->where('question_id', $result->question_id)
                    ->where('score', '>', $result->score)->count() + 1);
            } catch (QueryException $error) {
                report($error);
                $rank = null;
            }

            (clone $receipts)->where('key', $key)->update([
                'value' => json_encode(['resultId' => $result->id, 'rank' => $rank]
                    + ($counts === null ? [] : ['counts' => $counts]), JSON_THROW_ON_ERROR),
            ]);
            $receipts->where('expiration', '<=', now()->timestamp)->where('key', 'like', $prefix.'%')->delete();

            return [$result, $rank, $counts];
        }, 3);

        return response()->json(['resultId' => $result->id, 'score' => $result->score, 'rank' => $rank]
            + ($counts === null ? [] : ['counts' => $counts]));
    }
}
