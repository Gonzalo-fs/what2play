<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Mode;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('rejects admin game endpoints for guests', function () {
    $game = Game::factory()->create();

    $this->postJson('/api/v1/admin/games', [])->assertUnauthorized();
    $this->putJson('/api/v1/admin/games/'.$game->slug, [])->assertUnauthorized();
    $this->deleteJson('/api/v1/admin/games/'.$game->slug)->assertUnauthorized();
    $this->postJson('/api/v1/admin/games/'.$game->slug.'/image', [])->assertUnauthorized();
});

it('forbids non-admin users from managing games', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create();
    $developer = Developer::factory()->create();

    $payload = [
        'title' => 'Forbidden Game',
        'description' => 'Nope',
        'developer_id' => $developer->id,
        'genre_ids' => [],
        'mode_ids' => [],
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/admin/games', $payload)
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/admin/games/'.$game->slug, ['title' => 'X'])
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/admin/games/'.$game->slug)
        ->assertForbidden();

    $this->actingAs($user, 'sanctum')
        ->post('/api/v1/admin/games/'.$game->slug.'/image', [
            'image' => UploadedFile::fake()->image('logo.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertForbidden();
});

it('creates a game with generated unique slug and synced relations', function () {
    $admin = User::factory()->admin()->create();
    $developer = Developer::factory()->create();
    $genre = Genre::factory()->create();
    $mode = Mode::factory()->create();
    Game::factory()->create(['slug' => 'cool-game']);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/games', [
            'title' => 'Cool Game',
            'description' => 'A cool game',
            'developer_id' => $developer->id,
            'genre_ids' => [$genre->id],
            'mode_ids' => [$mode->id],
            'price' => 29.99,
            'score' => 8.5,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Cool Game')
        ->assertJsonPath('data.slug', 'cool-game-2')
        ->assertJsonPath('data.genres.0.id', $genre->id)
        ->assertJsonPath('data.modes.0.id', $mode->id);

    $this->assertDatabaseHas('games', [
        'title' => 'Cool Game',
        'slug' => 'cool-game-2',
    ]);
});

it('validates store payload', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin, 'sanctum')
        ->postJson('/api/v1/admin/games', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'description', 'developer_id', 'genre_ids', 'mode_ids']);
});

it('updates a game and syncs genres and modes', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create(['slug' => 'old-slug', 'title' => 'Old']);
    $oldGenre = Genre::factory()->create();
    $newGenre = Genre::factory()->create();
    $mode = Mode::factory()->create();
    $game->genres()->attach($oldGenre);

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/admin/games/old-slug', [
            'title' => 'New Title',
            'slug' => 'old-slug',
            'genre_ids' => [$newGenre->id],
            'mode_ids' => [$mode->id],
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'New Title')
        ->assertJsonPath('data.slug', 'old-slug')
        ->assertJsonPath('data.genres.0.id', $newGenre->id)
        ->assertJsonCount(1, 'data.genres')
        ->assertJsonPath('data.modes.0.id', $mode->id);
});

it('soft deletes a game and hides it from the public api', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create(['slug' => 'to-delete']);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/v1/admin/games/to-delete')
        ->assertNoContent();

    expect($game->fresh()->trashed())->toBeTrue();

    $this->getJson('/api/v1/games/to-delete')->assertNotFound();

    $this->actingAs($admin, 'sanctum')
        ->putJson('/api/v1/admin/games/to-delete', ['title' => 'Nope'])
        ->assertNotFound();
});

it('uploads and replaces a game image', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create(['slug' => 'with-image', 'image_path' => null]);

    $response = $this->actingAs($admin, 'sanctum')
        ->post('/api/v1/admin/games/with-image/image', [
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ], ['Accept' => 'application/json']);

    $response->assertOk();
    $path = $response->json('data.image_url');
    expect($path)->not->toBeNull();

    $storedPath = $game->fresh()->image_path;
    expect($storedPath)->not->toBeNull();
    Storage::disk('public')->assertExists($storedPath);

    $this->actingAs($admin, 'sanctum')
        ->post('/api/v1/admin/games/with-image/image', [
            'image' => UploadedFile::fake()->image('cover-2.png'),
        ], ['Accept' => 'application/json'])
        ->assertOk();

    Storage::disk('public')->assertMissing($storedPath);
    Storage::disk('public')->assertExists($game->fresh()->image_path);
});

it('rejects invalid game images', function () {
    $admin = User::factory()->admin()->create();
    $game = Game::factory()->create(['slug' => 'bad-image']);

    $this->actingAs($admin, 'sanctum')
        ->post('/api/v1/admin/games/bad-image/image', [
            'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);

    $this->actingAs($admin, 'sanctum')
        ->post('/api/v1/admin/games/bad-image/image', [
            'image' => UploadedFile::fake()->image('huge.jpg')->size(3000),
        ], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);
});
