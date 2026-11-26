<?php

namespace App\Http\Controllers;

use App\Support\TypingAttempt;
use App\Support\TypingQuestions;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

// タイピングの出題画面を返し、再読み込み時のプレイ再開を検証する。
class TypingGameController extends Controller
{
    public function show(Request $request, TypingQuestions $questions, TypingAttempt $attempts): View
    {
        $mode = $request->query('mode');
        $questionType = $request->query('type');
        $setId = filter_var($request->query('set'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        abort_unless(is_string($mode) && isset(TypingQuestions::LEVELS[$mode]), 404);
        abort_unless(is_string($questionType) && isset(TypingQuestions::TYPES[$questionType]), 404);
        abort_if($setId === false, 404);

        // URLの指定とDB上のモード・種別を照合し、公開中のセットだけを出題する。
        $set = $questions->published()
            ->where('level', TypingQuestions::LEVELS[$mode])
            ->where('type', TypingQuestions::TYPES[$questionType])
            ->findOrFail($setId);
        $playItems = $questions->playItems($set);
        abort_if($playItems === null, 404);
        $userId = (int) $request->user()->getAuthIdentifier();
        // 出題内容から識別値を作り、問題の変更を再開時や実践の保存時に検出する。
        $revision = hash('sha256', json_encode($playItems, JSON_THROW_ON_ERROR));

        return view('typing.game', [
            'mode' => $mode,
            'modeName' => $mode === 'practice' ? '実践タイピング' : 'ゲームタイピング',
            'questionType' => $questionType,
            'setId' => $setId,
            'attempt' => $attempts->issue($userId, $setId, $mode, $questionType, $revision),
            'playerId' => $userId,
            'questionRevision' => $revision,
            'resultUrl' => route('typing.result', ['mode' => $mode, 'type' => $questionType, 'set' => $setId]),
            'selectUrl' => route('typing.select'),
            'questions' => $playItems,
        ]);
    }

    public function resume(Request $request, TypingAttempt $attempts): Response
    {
        $tokens = $request->validate([
            'attempt' => ['required', 'string', 'max:2048'],
            'currentAttempt' => ['required', 'string', 'max:2048'],
        ]);
        $userId = (int) $request->user()->getAuthIdentifier();
        $previous = $attempts->read($tokens['attempt'], $userId);
        $current = $attempts->read($tokens['currentAttempt'], $userId);

        // 現在の画面が発行した署名済みトークンと照合し、古い問題データや別のセットでの再開を拒否する。
        foreach (['setId', 'mode', 'type', 'revision'] as $field) {
            abort_unless(isset($previous[$field], $current[$field]) && $previous[$field] === $current[$field], 422);
        }

        return response()->noContent();
    }
}
