<?php

namespace Database\Seeders;

use App\Models\ProjectStatus;
use Illuminate\Database\Seeder;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Planning'],
            ['name' => 'In Progress'],
            ['name' => 'Testing'],
            ['name' => 'On Hold'],
            ['name' => 'Completed'],
        ];

        foreach ($statuses as $status) {
            ProjectStatus::create($status);
        }
    }
}
