<?php

namespace App\Support;

use App\Models\Detail;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

class TypingQuestions
{
    public const LEVELS = ['practice' => 1, 'game' => 2];

    public const TYPES = ['word' => 2, 'long' => 1];

    public function published(): Builder
    {
        return Question::query()
            ->where('release', 1)
            ->with('details:id,question_id,text,reading');
    }

    public function playItems(Question $set): ?array
    {
        if ($set->details->isEmpty()) {
            return null;
        }

        $items = [];
        foreach ($set->details->sortBy('id') as $detail) {
            $item = $this->playItem($detail);
            if ($item === null) {
                return null;
            }
            $items[] = $item;
        }

        return $items;
    }

    private function playItem(Detail $detail): ?array
    {
        $reading = preg_replace('/\A[\s\p{Z}]+|[\s\p{Z}]+\z/u', '', $detail->reading ?? '');

        // 出題可否は読みだけで判定し、本文は登録された表記のまま表示する。
        if ($reading === null || !preg_match('/\A[ぁ-ろわをんァ-ロワヲンー、。・「」　！？（）：；／－\x20-\x7e]+\z/u', $reading)) {
            return null;
        }

        return ['text' => $detail->text, 'kana' => $reading];
    }
}
