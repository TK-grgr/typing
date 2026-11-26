<?php

namespace App\Support;

use OverflowException;

// 実践・ゲームの入力結果を、JavaScriptと同じ計算式で採点する。
class TypingScore
{
    public function calculate(string $mode, string $type, int $correct, int $miss, float $elapsedMs): int
    {
        // C は正しい文字数（ゲームは正打数）、M はミス数。T が 0 以下なら 0.01 秒で計算する。
        // A は正確率（0～1）、V は1分あたりの入力速度。C と M がともに 0 なら A は 0。
        $C = $correct;
        $M = $miss;
        $seconds = $elapsedMs / 1000;
        $T = $seconds > 0 ? $seconds : 0.01;
        $A = $C + $M === 0 ? 0 : $C / ($C + $M);
        $V = $C / $T * 60;

        if ($mode === 'practice' || $type === 'word') {
            // 実践の短文・長文とゲームの単語。V × 10 × A² で正確さを速度点に反映する。
            $score = $V * 10 * $A ** 2;
        } else {
            // ゲーム×長文。V × 5 + V × 5 × A²。速度の基本点に正確さによる加点を足す。
            $score = $V * 5 + $V * 5 * $A ** 2;
        }

        // 途中は丸めず、最後に絶対値を四捨五入して符号を戻す。JavaScript と同じく負の半端値はゼロから遠ざける。
        $absolute = abs($score);
        $whole = floor($absolute);
        $rounded = $whole + ($absolute - $whole >= 0.5 ? 1 : 0);
        $signed = $score < 0 ? -$rounded : $rounded;
        if (!is_finite($V) || !is_finite($signed) || $signed >= -(float) PHP_INT_MIN || $signed < (float) PHP_INT_MIN) {
            throw new OverflowException('Score cannot be represented as an integer.');
        }

        return (int) $signed;
    }
}
