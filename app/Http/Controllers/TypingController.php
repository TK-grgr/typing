<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TypingController extends Controller
{
    public function select(): View
    {
        $tabs = [
            ['id' => 'practice', 'label' => '実践タイピング'],
            ['id' => 'game', 'label' => 'ゲームタイピング'],
            ['id' => 'program', 'label' => 'プログラムタイピング'],
            ['id' => 'public', 'label' => 'パブリックタイピング'],
        ];

        // 表示名は未設定。将来は各itemsをDBから取得した問題一覧に置き換える。
        $groups = [
            [
                'id' => 'word',
                'label' => '単語',
                'items' => array_map(fn (int $number) => ['id' => 'word-'.$number], range(1, 6)),
            ],
            [
                'id' => 'passage',
                'label' => '長文',
                'items' => array_map(fn (int $number) => ['id' => 'passage-'.$number], range(1, 6)),
            ],
        ];

        return view('typing.select', [
            'tabs' => $tabs,
            'groups' => $groups,
            'initialTab' => 'practice',
            'initialQuestion' => 'word-1',
        ]);
    }
}
