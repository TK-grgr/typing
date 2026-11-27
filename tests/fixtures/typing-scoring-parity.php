<?php

use App\Support\TypingMinimumKeystrokes;
use App\Support\TypingPractice;
use App\Support\TypingScore;

require __DIR__.'/../../vendor/autoload.php';

// JavaScriptとの比較に使う採点・最短打鍵数・文章照合の結果を返す。
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$score = new TypingScore;
$minimum = new TypingMinimumKeystrokes;
echo json_encode([
    'scores' => array_map(fn ($play) => $score->calculate($play['mode'], $play['type'], $play['correct'], $play['miss'],
        $play['elapsedMs']), $input['plays']),
    'minimums' => array_map(fn ($reading) => $minimum->count($reading), $input['readings']),
    'comparisons' => array_map(fn ($pair) => (new TypingPractice)->compare(...$pair), $input['comparisons'] ?? []),
], JSON_THROW_ON_ERROR);
