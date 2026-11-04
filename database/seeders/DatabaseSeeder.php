<?php

namespace Database\Seeders;

use App\Models\Game;
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

        $sampleGame = Game::query()
            ->with(['genres', 'modes'])
            ->whereHas('genres')
            ->whereHas('modes')
            ->where(function ($query): void {
                $query->whereNull('price')->orWhere('price', '<=', 30);
            })
            ->inRandomOrder()
            ->firstOrFail();

        $user->genrePreferences()->attach($sampleGame->genres->take(2)->pluck('id'));

        $singlePlayer = Mode::query()->where('name', 'Single player')->first();
        $modeIds = $sampleGame->modes->pluck('id');
        if ($singlePlayer && $modeIds->contains($singlePlayer->id)) {
            $user->modePreferences()->attach($singlePlayer->id);
        } else {
            $user->modePreferences()->attach($modeIds->take(1));
        }

        $user->library()->attach(
            Game::query()->whereKeyNot($sampleGame->id)->inRandomOrder()->limit(3)->pluck('id'),
            ['saved' => true, 'completed' => false]
        );
    }
}
