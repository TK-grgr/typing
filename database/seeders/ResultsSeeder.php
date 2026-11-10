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
        Result::create([
            'user_id'      => 1,
            'question_id'  => 1,
            'score'        => 350,
            'correct'      => 45,
            'miss'         => 3,
            'input_time'   => 60,
        ]);
    }
}