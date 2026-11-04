<?php

use App\Models\Game;
use App\Models\User;

it('rejects library endpoints for guests', function () {
    $game = Game::factory()->create();

    $this->getJson('/api/v1/me/library')->assertUnauthorized();
    $this->putJson('/api/v1/me/library/'.$game->slug)->assertUnauthorized();
    $this->deleteJson('/api/v1/me/library/'.$game->slug)->assertUnauthorized();
});

it('lists only the authenticated user library games', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $ownGame = Game::factory()->create(['slug' => 'own-game']);
    $otherGame = Game::factory()->create(['slug' => 'other-game']);

    $user->library()->attach($ownGame->id, ['completed' => false, 'saved' => true]);
    $other->library()->attach($otherGame->id, ['completed' => false, 'saved' => true]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/me/library');

    $response->assertOk()
        ->assertJsonStructure(['data', 'links', 'meta']);

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toBe(['own-game'])
        ->and($response->json('data.0.completed'))->toBeFalse()
        ->and($response->json('data.0.saved'))->toBeTrue();
});

it('filters library by completed and saved flags', function () {
    $user = User::factory()->create();
    $completed = Game::factory()->create(['slug' => 'completed']);
    $savedOnly = Game::factory()->create(['slug' => 'saved-only']);
    $unsaved = Game::factory()->create(['slug' => 'unsaved']);

    $user->library()->attach($completed->id, ['completed' => true, 'saved' => true]);
    $user->library()->attach($savedOnly->id, ['completed' => false, 'saved' => true]);
    $user->library()->attach($unsaved->id, ['completed' => false, 'saved' => false]);

    $completedSlugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me/library?completed=1')
            ->json('data')
    )->pluck('slug')->all();

    $savedSlugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/me/library?saved=1')
            ->json('data')
    )->pluck('slug')->all();

    expect($completedSlugs)->toBe(['completed'])
        ->and($savedSlugs)->toContain('completed', 'saved-only')
        ->and($savedSlugs)->not->toContain('unsaved');
});

it('adds a game with default flags', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'new-entry']);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/library/new-entry')
        ->assertOk()
        ->assertJsonPath('data.slug', 'new-entry')
        ->assertJsonPath('data.completed', false)
        ->assertJsonPath('data.saved', true);

    $pivot = $user->library()->where('games.id', $game->id)->first()->pivot;

    expect((bool) $pivot->completed)->toBeFalse()
        ->and((bool) $pivot->saved)->toBeTrue();
});

it('updates library flags and is idempotent', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'flagged']);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/library/flagged', [
            'completed' => true,
            'saved' => false,
        ])
        ->assertOk()
        ->assertJsonPath('data.completed', true)
        ->assertJsonPath('data.saved', false);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/library/flagged', [
            'completed' => true,
            'saved' => false,
        ])
        ->assertOk()
        ->assertJsonPath('data.completed', true)
        ->assertJsonPath('data.saved', false);

    expect($user->library()->count())->toBe(1);
});

it('deletes a library entry idempotently', function () {
    $user = User::factory()->create();
    $game = Game::factory()->create(['slug' => 'removable']);
    $user->library()->attach($game->id, ['completed' => false, 'saved' => true]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/me/library/removable')
        ->assertNoContent();

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/me/library/removable')
        ->assertNoContent();

    expect($user->library()->count())->toBe(0);
});

it('returns not found for unknown or soft deleted games', function () {
    $user = User::factory()->create();
    $deleted = Game::factory()->create(['slug' => 'gone']);
    $deleted->delete();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/library/missing')
        ->assertNotFound();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/library/gone')
        ->assertNotFound();
});

it('validates library flags as booleans', function () {
    $user = User::factory()->create();
    Game::factory()->create(['slug' => 'validate-me']);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/library/validate-me', [
            'completed' => 'yes',
            'saved' => 'nope',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['completed', 'saved']);
});
