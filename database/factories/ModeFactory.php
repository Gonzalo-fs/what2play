<?php

namespace Database\Factories;

use App\Models\Mode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mode>
 */
class ModeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
