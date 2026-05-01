<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specializations = [
            'Frontend Developer',
            'Backend Developer',
            'Fullstack Engineer',
            'UI/UX Designer',
            'Mobile Developer',
            'DevOps Engineer',
            'QA Tester',
        ];

        foreach ($specializations as $name) {
            \App\Models\Specialization::create(['name' => $name]);
        }
    }
}
