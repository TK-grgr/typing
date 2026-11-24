<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => '管理者',
            'email'      => 'kanrisya@example.com',
            'password'  => 'admin',
            'school_id' => 1,
            'class_id'  => 1,
            'role'      => 1,
        ]);
    }
}
