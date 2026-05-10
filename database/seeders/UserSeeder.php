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
        User::create([
            'name' => 'Project Manager',
            'email' => 'pm@simcr.com',
            'password' => Hash::make('password'),
            'role' => 'pm',
        ]);
        User::create([
            'name' => 'Owner',
            'email' => 'owner@simcr.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);
    }
}
