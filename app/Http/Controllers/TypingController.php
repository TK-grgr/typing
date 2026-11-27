<?php

namespace App\Http\Controllers;

use App\Support\TypingQuestions;
use Illuminate\View\View;

// 公開されている問題セットからタイピング選択画面を組み立てる。
class TypingController extends Controller
{
    public function select(TypingQuestions $questions): View
    {
        $tabs = [
            ['id' => 'practice', 'label' => '実践タイピング'],
            ['id' => 'game', 'label' => 'ゲームタイピング'],
        ];

        $sets = $questions->published()
            ->whereIn('level', array_values(TypingQuestions::LEVELS))
            ->whereIn('type', array_values(TypingQuestions::TYPES))
            ->orderBy('id')->get();

        $groups = [
            'word' => ['label' => '単語', 'items' => []],
            'long' => ['label' => '長文', 'items' => []],
        ];
        // DB上のモード・種別を画面のグループに変換し、セット単位で出題可否を判定する。
        foreach ($sets as $set) {
            $mode = array_search((int) $set->level, TypingQuestions::LEVELS, true);
            $type = array_search((int) $set->type, TypingQuestions::TYPES, true);
            $groups[$type]['items'][] = [
                'id' => $set->id,
                'title' => $set->title,
                'mode' => $mode,
                'playable' => $questions->playItems($set) !== null,
            ];
        }

        // 従来どおり実践タブを初期表示し、単語を優先して選択する。
        $initialQuestion = collect(array_merge($groups['word']['items'], $groups['long']['items']))
            ->first(fn ($item) => $item['mode'] === 'practice' && $item['playable']);

        return view('typing.select', [
            'tabs' => $tabs,
            'groups' => $groups,
            'initialTab' => 'practice',
            'initialQuestion' => $initialQuestion['id'] ?? null,
        ]);
    }
}
