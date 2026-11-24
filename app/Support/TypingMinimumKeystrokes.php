<?php

namespace App\Support;

class TypingMinimumKeystrokes
{
    // typing-romaji.js の組み合わせごとに最短候補の長さだけを持つ。
    private const PAIRS = [
        'きゃ', 'きゅ', 'きょ', 'しゃ', 'しゅ', 'しょ', 'ちゃ', 'ちゅ', 'ちょ',
        'にゃ', 'にゅ', 'にょ', 'ひゃ', 'ひゅ', 'ひょ', 'みゃ', 'みゅ', 'みょ', 'りゃ', 'りゅ', 'りょ',
        'ぎゃ', 'ぎゅ', 'ぎょ', 'びゃ', 'びゅ', 'びょ', 'ぴゃ', 'ぴゅ', 'ぴょ', 'てぃ', 'でぃ',
    ];

    public function count(string $reading): int
    {
        // 出題可能な文字に限定し、カタカナをひらがなに揃える。互換記号は各 1 打鍵。
        $reading = preg_replace_callback('/[ァ-ロワヲン]/u',
            fn ($match) => mb_chr(mb_ord($match[0], 'UTF-8') - 96, 'UTF-8'), $reading);
        $kana = mb_str_split($reading, 1, 'UTF-8');
        $length = count($kana);
        $best = [$length => 0];
        $consonant = [];

        // 後ろから計算し、各位置の最短と、促音で先頭子音を重ねられる場合の最短を保持する。
        for ($i = $length - 1; $i >= 0; $i--) {
            $char = $kana[$i];
            $next = $kana[$i + 1] ?? '';
            if ($char === 'ん') {
                $best[$i] = (preg_match('/[あいうえおやゆよん]/u', $next) ? 2 : 1) + $best[$i + 1];
                continue;
            }
            if ($char === 'っ') {
                $best[$i] = min(3 + $best[$i + 1], isset($consonant[$i + 1]) ? 1 + $consonant[$i + 1] : PHP_INT_MAX);
                continue;
            }

            $syllable = preg_match('/[ぁ-ろわを]/u', $char) && !preg_match('/[あいうえお]/u', $char);
            // 小さい母音の xa、xi などは 2 打鍵。小さいゃ・ゅ・ょの xya などは 3 打鍵。
            $best[$i] = (preg_match('/[ゃゅょ]/u', $char) ? 3 : ($syllable ? 2 : 1)) + $best[$i + 1];
            if (($syllable && !preg_match('/[なにぬねの]/u', $char)) || preg_match('/[bcdfghjklmpqrstvwxyz]/i', $char)) {
                $consonant[$i] = $best[$i];
            }
            $pair = $char.$next;
            if (in_array($pair, self::PAIRS, true) || in_array($pair, ['じゃ', 'じゅ', 'じょ', 'ふぁ'], true)) {
                $pairLength = (in_array($pair, self::PAIRS, true) ? 3 : 2) + $best[$i + 2];
                $best[$i] = min($best[$i], $pairLength);
                if ($char !== 'に') {
                    $consonant[$i] = min($consonant[$i] ?? PHP_INT_MAX, $pairLength);
                }
            }
        }

        return $best[0];
    }
}
