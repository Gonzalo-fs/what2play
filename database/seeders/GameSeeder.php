<?php

namespace Database\Seeders;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Mode;
use Illuminate\Database\Seeder;

/**
 * Seeds fictional sample developers and games (not real data).
 */
class GameSeeder extends Seeder
{
    public function run(): void
    {
        $genres = Genre::all();
        $modes = Mode::all();

        Developer::factory()
            ->count(8)
            ->has(Game::factory()->count(5))
            ->create();

        Game::all()->each(function (Game $game) use ($genres, $modes) {
            $game->genres()->attach($genres->random(fake()->numberBetween(1, 3)));
            $game->modes()->attach($modes->random(fake()->numberBetween(1, 2)));
        });
    }
}
