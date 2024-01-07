<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            "name" => "staff",
            "email" => "staff@gmail.com",
            "password" => Hash::make("staff"),
            "role" => "staff",
        ]);
        User::create([
            "name" => "guru",
            "email" => "guru@gmail.com",
            "password" => Hash::make("guru"),
            "role" => "guru",
        ]);
    }
}
