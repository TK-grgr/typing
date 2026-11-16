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
        Question::create([
            'title' => 'テスト用',
            'no'      => 1,
            'type' => 1,
            'level' => 1,
            'release'=>1,
        ]);
    }
}
