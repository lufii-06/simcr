<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SpecializationSeeder::class,
            DeveloperStatusSeeder::class,
            ProjectStatusSeeder::class,
        ]);

        // Create dummy data
        \App\Models\User::factory(5)->create(); // Mixed developer/pm users
        \App\Models\Client::factory(3)->create(); // 3 Clients
        \App\Models\Developer::factory(7)->create(); // 7 Developers
    }
}
