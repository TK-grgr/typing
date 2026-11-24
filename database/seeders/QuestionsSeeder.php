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
            'type' => 1,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => '実践長文②',
            'type' => 1,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => '実践短文①',
            'type' => 2,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => '実践短文②',
            'type' => 2,
            'level' => 1,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム長文①',
            'type' => 1,
            'level' => 2,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム長文②',
            'type' => 1,
            'level' => 2,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム短文①',
            'type' => 2,
            'level' => 2,
            'release' => 1,
        ]);

        Question::create([
            'title' => 'ゲーム短文②',
            'type' => 2,
            'level' => 2,
            'release' => 1,
        ]);
    }
}
