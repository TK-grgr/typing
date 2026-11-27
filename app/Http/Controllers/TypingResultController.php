<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

// プレイ直後の結果画面に、モード別の表示項目と再プレイ条件を渡す。
class TypingResultController extends Controller
{
    public function show(Request $request): View
    {
        $mode = $request->query('mode') === 'practice' ? 'practice' : 'game';
        $questionType = $request->query('type') === 'long' ? 'long' : 'word';
        $setId = filter_var($request->query('set'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($setId === false, 404);

        // 実践は一致した文字数と誤字・脱字・余字、ゲームは正打数とミス回数を表示する。
        $metrics = [
            'score' => '総合スコア',
            'correct' => '正しい打鍵数',
        ];

        if ($mode === 'game') {
            $metrics['miss'] = 'ミス回数';
        } else {
            $metrics['correct'] = '正しい文字数';
            $metrics += ['typo' => '誤字', 'omission' => '脱字', 'extra' => '余字'];
        }

        $metrics += [
            'elapsed' => '経過時間',
            'accuracy' => '正確率',
            'speed' => '入力速度',
            'rank' => '今回のランキング順位',
        ];

        return view('typing.result', [
            'playerId' => (string) $request->user()->getAuthIdentifier(),
            'mode' => $mode,
            'modeName' => $mode === 'practice' ? '実践タイピング' : 'ゲームタイピング',
            'questionType' => $questionType,
            'setId' => $setId,
            'questionTypeName' => $questionType === 'long' ? '長文' : '短文',
            'setName' => $setId,
            'metrics' => $metrics,
        ]);
    }
}
