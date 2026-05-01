<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Developer>
 */
class DeveloperFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'developer'])->id,
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'specialization_id' => \App\Models\Specialization::inRandomOrder()->first()?->id ?? \App\Models\Specialization::factory(),
            'portfolio_url' => fake()->url(),
        ];
    }
}
