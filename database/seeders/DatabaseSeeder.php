<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Developer;
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
            TaskStatusSeeder::class,
        ]);

        User::factory(5)->create();
        Client::factory(3)->create();
        Developer::factory(7)->create();
    }
}
