<?php

use App\Support\TypingMinimumKeystrokes;
use App\Support\TypingScore;

require __DIR__.'/../../vendor/autoload.php';

$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
$score = new TypingScore;
$minimum = new TypingMinimumKeystrokes;
echo json_encode([
    'scores' => array_map(fn ($play) => $score->calculate($play['mode'], $play['type'], $play['correct'], $play['miss'],
        $play['elapsedMs'], $play['minimumKeystrokes']), $input['plays']),
    'minimums' => array_map(fn ($reading) => $minimum->count($reading), $input['readings']),
], JSON_THROW_ON_ERROR);
