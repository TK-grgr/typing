<?php

namespace Database\Seeders;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Question::truncate();

        Question::create([
            'title' => '実践長文①',
            'no' => 1,
            'type' => 1,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => '実践長文②',
            'no' => 2,
            'type' => 1,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => '実践短文①',
            'no' => 3,
            'type' => 2,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => '実践短文②',
            'no' => 4,
            'type' => 2,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム長文①',
            'no' => 5,
            'type' => 1,
            'level' => 2,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム長文②',
            'no' => 6,
            'type' => 1,
            'level' => 2,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム短文①',
            'no' => 7,
            'type' => 2,
            'level' => 2,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム短文②',
            'no' => 8,
            'type' => 2,
            'level' => 2,
            'release' => 1,
        ]);
    }
}
