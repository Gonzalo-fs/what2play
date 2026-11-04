<?php

namespace Database\Factories;

use App\Models\Developer;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(fake()->numberBetween(1, 3), true));

        return [
            'developer_id' => Developer::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(),
            'release_year' => fake()->numberBetween(1995, 2026),
            'disclaimer' => fake()->optional(0.2)->sentence(),
            'video_url' => fake()->optional()->url(),
            'price' => fake()->optional(0.85)->randomFloat(2, 4.99, 69.99),
            'buy_url' => fake()->optional()->url(),
            'difficulty' => fake()->randomElement(['easy', 'medium', 'hard']),
            'audience' => fake()->randomElement(['everyone', 'teen', 'mature']),
            'graphics' => fake()->randomElement(['pixel art', 'stylized', 'realistic', 'low poly']),
            'requirements' => fake()->optional()->sentence(),
            'requirements_specs' => fake()->optional()->sentence(),
            'game_length_minutes' => fake()->numberBetween(60, 6000),
            'score' => fake()->randomFloat(1, 3, 10),
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => ['price' => null]);
    }
}
