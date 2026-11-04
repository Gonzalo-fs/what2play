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

        $user->genrePreferences()->attach(Genre::inRandomOrder()->limit(2)->pluck('id'));
        $user->modePreferences()->attach(Mode::where('name', 'Single player')->pluck('id'));
        $user->library()->attach(Game::inRandomOrder()->limit(3)->pluck('id'), ['saved' => true]);
    }
}
