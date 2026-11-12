<?php

namespace Database\Seeders;

use App\Models\Result;
use Illuminate\Database\Seeder;

class ResultsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {

            Result::create([
                'user_id'      => 2,
                'question_id'  => 1,
                'score'        => rand(100, 1000),
                'correct'      => rand(20, 100),
                'miss'         => rand(0, 20),
                'input_time'   => rand(30, 180),
            ]);

        }
    }
}