<?php

namespace App\Support;

use App\Models\Detail;
use App\Models\Question;
use Illuminate\Database\Eloquent\Builder;

// 公開済みの問題セットと明細を取得し、画面・保存処理で共通の出題条件を適用する。
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

    // 問題の順序を明細IDで揃える。実践では本文、ゲームでは本文と読みを使う。
    public function playItems(Question $set): ?array
    {
        if ($set->details->isEmpty()) {
            return null;
        }

        $items = [];
        foreach ($set->details->sortBy('id') as $detail) {
            if ((int) $set->level === self::LEVELS['practice']) {
                if (trim($detail->text) === '') {
                    return null;
                }
                $items[] = ['text' => $detail->text];

                continue;
            }
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

        // ゲームの出題可否は読みで判定し、本文は登録された表記のまま表示する。
        if ($reading === null || !preg_match('/\A[ぁ-ろわをんァ-ロワヲンー、。・「」　！？（）：；／－\x20-\x7e]+\z/u', $reading)) {
            return null;
        }

        return ['text' => $detail->text, 'kana' => $reading];
    }
}
