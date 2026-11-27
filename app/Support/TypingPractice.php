<?php

namespace App\Support;

// 実践で提出された回答から、保存用の一致数と誤字・脱字・余字を求める。
class TypingPractice
{
    public const MAX_ANSWER_LENGTH = 4096;

    // ブラウザの入力欄と同じUTF-16の長さで、回答の上限を確認する。
    public function accepts(mixed $answer): bool
    {
        return is_string($answer) && strlen(mb_convert_encoding($answer, 'UTF-16LE', 'UTF-8')) <= self::MAX_ANSWER_LENGTH * 2;
    }

    // 出題データと同じ順序の回答を照合し、全問分の件数を合算する。
    public function total(array $items, array $answers): array
    {
        $total = ['correct' => 0, 'typo' => 0, 'omission' => 0, 'extra' => 0];
        foreach ($items as $index => $item) {
            foreach ($this->compare($item['text'], $answers[$index]) as $key => $count) {
                $total[$key] += $count;
            }
        }

        return $total;
    }

    // JavaScriptと同じく、編集回数、一致数、斜め・脱字・余字の順で経路を確定する。
    public function compare(string $text, string $answer): array
    {
        $target = mb_str_split(str_replace(["\r\n", "\r"], "\n", $text), 1, 'UTF-8');
        $input = mb_str_split(str_replace(["\r\n", "\r"], "\n", $answer), 1, 'UTF-8');
        $weight = count($target) + 1;
        $row = array_map(fn ($i) => $i * $weight, range(0, $weight - 1));
        $steps = [null];
        foreach ($input as $j => $character) {
            $next = [($j + 1) * $weight];
            $moves = str_repeat("\0", $weight);
            for ($i = 1; $i < $weight; $i++) {
                $diagonal = $row[$i - 1] + ($target[$i - 1] === $character ? -1 : $weight);
                $omission = $next[$i - 1] + $weight;
                $extra = $row[$i] + $weight;
                $next[$i] = min($diagonal, $omission, $extra);
                $moves[$i] = $next[$i] === $diagonal ? "\0" : ($next[$i] === $omission ? "\1" : "\2");
            }
            $steps[] = $moves;
            $row = $next;
        }

        $counts = ['correct' => 0, 'typo' => 0, 'omission' => 0, 'extra' => 0];
        $i = count($target);
        $j = count($input);
        while ($i && $j) {
            $move = $steps[$j][$i];
            if ($move === "\0") {
                $counts[$target[--$i] === $input[--$j] ? 'correct' : 'typo']++;
            } elseif ($move === "\1") {
                $counts['omission']++;
                $i--;
            } else {
                $counts['extra']++;
                $j--;
            }
        }
        $counts['omission'] += $i;
        $counts['extra'] += $j;

        return $counts;
    }
}
