<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Genre;
use App\Models\Mode;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            GenreSeeder::class,
            ModeSeeder::class,
            DeveloperSeeder::class,
            GameSeeder::class,
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'max_price' => 30,
        ]);

        $genreIds = Genre::query()
            ->whereIn('name', ['action and adventure', 'story based'])
            ->pluck('id');

        $soloModeId = Mode::query()->where('name', 'Solo')->value('id');

        $user->genrePreferences()->sync($genreIds);
        $user->modePreferences()->sync([$soloModeId]);

        $hades = Game::query()->where('slug', 'hades')->firstOrFail();
        $witcher = Game::query()->where('slug', 'the-witcher-3-wild-hunt')->firstOrFail();

        $user->library()->sync([
            $hades->id => ['saved' => true, 'completed' => true],
            $witcher->id => ['saved' => true, 'completed' => false],
        ]);
    }
}
