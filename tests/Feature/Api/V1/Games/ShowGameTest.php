<?php

use App\Models\Game;
use App\Models\Genre;
use App\Models\Mode;
use Illuminate\Support\Facades\Storage;

it('shows a game by slug with developer genres and modes', function () {
    $game = Game::factory()->create([
        'slug' => 'example-game',
        'title' => 'Example Game',
    ]);
    $genre = Genre::factory()->create(['name' => 'Action']);
    $mode = Mode::factory()->create(['name' => 'Co-op']);

    $game->genres()->attach($genre);
    $game->modes()->attach($mode);

    $this->getJson('/api/v1/games/example-game')
        ->assertOk()
        ->assertJsonPath('data.slug', 'example-game')
        ->assertJsonPath('data.title', 'Example Game')
        ->assertJsonPath('data.developer.id', $game->developer_id)
        ->assertJsonPath('data.genres.0.id', $genre->id)
        ->assertJsonPath('data.modes.0.id', $mode->id)
        ->assertJsonPath('data.image_url', null);
});

it('returns image_url when image_path is set', function () {
    Storage::fake('public');

    $game = Game::factory()->create([
        'slug' => 'with-image',
        'image_path' => 'games/cover.jpg',
    ]);

    $this->getJson('/api/v1/games/with-image')
        ->assertOk()
        ->assertJsonPath('data.image_url', Storage::disk('public')->url($game->image_path));
});

it('returns not found for an unknown slug', function () {
    $this->getJson('/api/v1/games/missing-game')
        ->assertNotFound();
});

it('returns not found for a soft deleted game', function () {
    $game = Game::factory()->create(['slug' => 'deleted-game']);
    $game->delete();

    $this->getJson('/api/v1/games/deleted-game')
        ->assertNotFound();
});

it('allows guests to view a game', function () {
    Game::factory()->create(['slug' => 'public-game']);

    $this->getJson('/api/v1/games/public-game')
        ->assertOk();
});
