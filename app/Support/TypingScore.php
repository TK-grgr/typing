<?php

namespace App\Support;

use OverflowException;

class TypingScore
{
    public function calculate(string $mode, string $type, int $correct, int $miss, float $elapsedMs, int $minimum): int
    {
        // C は正打数、M はミス数、N は最短打鍵数。T はミリ秒から換算した秒数。
        // T が 0 以下なら 0.01 秒を使い、総打鍵数が 0 なら正確率 A を 0 としてゼロ除算を防ぐ。
        // A は正打数を総打鍵数で割った比率、V は正打数を 1 分あたりに換算した速度。
        $C = $correct;
        $M = $miss;
        $N = $minimum;
        $seconds = $elapsedMs / 1000;
        $T = $seconds > 0 ? $seconds : 0.01;
        $A = $C + $M === 0 ? 0 : $C / ($C + $M);
        $V = $C / $T * 60;

        if ($mode === 'game' && $type === 'word') {
            // ゲーム×単語。V × 10 × A²。速度点全体に正確率の二乗を掛けてミスの影響を強く反映する。
            $score = $V * 10 * $A ** 2;
        } elseif ($mode === 'game') {
            // ゲーム×長文。V × 5 + V × 5 × A²。速度の基本点に正確さによる加点を足す。
            $score = $V * 5 + $V * 5 * $A ** 2;
        } elseif ($type === 'word') {
            // 実践×単語。(C − M) ÷ T × 1000。正打とミスの差を速度に換算し、ミス超過時の負点も残す。
            $score = ($C - $M) / $T * 1000;
        } else {
            // 実践×長文。V × A × (1 + N ÷ 250)。速度と正確さに問題セットの長さを反映する。
            // 読みから求めた最短打鍵数 N を使い、基準の 250 打鍵では補正倍率が 2 になる。
            $score = $V * $A * (1 + $N / 250);
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
