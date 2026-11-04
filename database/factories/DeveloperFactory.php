<?php

namespace Database\Factories;

use App\Models\Developer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Developer>
 */
class DeveloperFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Studios',
        ];
    }
}
