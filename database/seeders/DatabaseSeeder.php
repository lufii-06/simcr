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

        // Create clients and developers (which automatically creates their associated user accounts)
        Client::factory(3)->create();
        Developer::factory(7)->create();

        // Create some additional users with roles that do not require profile entries
        User::factory(2)->create(['role' => 'pm']);
        User::factory(1)->create(['role' => 'owner']);
    }
}
