<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User 1
        User::factory()->create([
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'test1@test.com',
            'password' => Hash::make('password'), // Password is 'password'
        ]);

        // User 2
        User::factory()->create([
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => 'test2@test.com',
            'password' => Hash::make('123123'),
        ]);
    }
}
