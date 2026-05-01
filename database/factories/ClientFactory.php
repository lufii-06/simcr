<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->create(['role' => 'client'])->id,
            'company_name' => fake()->company(),
            'main_contact' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }
}
