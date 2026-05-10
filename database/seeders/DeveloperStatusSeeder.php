<?php

namespace Database\Seeders;

use App\Models\DeveloperStatus;
use Illuminate\Database\Seeder;

class DeveloperStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['name' => 'Project Leader'],
            ['name' => 'Developer'],
            ['name' => 'Maintainer'],
            ['name' => 'Tech Lead'],
            ['name' => 'QA / Tester'],
        ];

        foreach ($statuses as $status) {
            DeveloperStatus::create($status);
        }
    }
}
