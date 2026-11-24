<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TypingResultController extends Controller
{
    public function show(Request $request): View
    {
        $mode = $request->query('mode') === 'practice' ? 'practice' : 'game';
        $questionType = $request->query('type') === 'long' ? 'long' : 'word';
        $setId = filter_var($request->query('set'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        abort_if($setId === false, 404);

        $metrics = [
            'score' => '総合スコア',
            'correct' => '正しい打鍵数',
        ];

        if ($mode === 'game') {
            $metrics['miss'] = 'ミス回数';
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
