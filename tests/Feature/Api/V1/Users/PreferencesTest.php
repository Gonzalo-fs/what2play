<?php

use App\Models\Genre;
use App\Models\Mode;
use App\Models\User;

it('rejects preferences for guests', function () {
    $this->getJson('/api/v1/me/preferences')
        ->assertUnauthorized();

    $this->putJson('/api/v1/me/preferences', [
        'genre_ids' => [],
        'mode_ids' => [],
        'max_price' => null,
    ])->assertUnauthorized();
});

it('shows default preferences', function () {
    $user = User::factory()->create(['max_price' => null]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/me/preferences')
        ->assertOk()
        ->assertJsonPath('data.genres', [])
        ->assertJsonPath('data.modes', [])
        ->assertJsonPath('data.max_price', null);
});

it('updates preferences replacing the full set', function () {
    $user = User::factory()->create(['max_price' => 10]);
    $oldGenre = Genre::factory()->create(['name' => 'Old Genre']);
    $oldMode = Mode::factory()->create(['name' => 'Old Mode']);
    $newGenre = Genre::factory()->create(['name' => 'New Genre']);
    $newMode = Mode::factory()->create(['name' => 'New Mode']);

    $user->genrePreferences()->attach($oldGenre);
    $user->modePreferences()->attach($oldMode);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/preferences', [
            'genre_ids' => [$newGenre->id],
            'mode_ids' => [$newMode->id],
            'max_price' => 25.50,
        ])
        ->assertOk()
        ->assertJsonPath('data.genres.0.id', $newGenre->id)
        ->assertJsonPath('data.modes.0.id', $newMode->id)
        ->assertJsonPath('data.max_price', '25.50')
        ->assertJsonMissing(['id' => $oldGenre->id]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/me/preferences')
        ->assertOk()
        ->assertJsonCount(1, 'data.genres')
        ->assertJsonCount(1, 'data.modes')
        ->assertJsonPath('data.genres.0.id', $newGenre->id)
        ->assertJsonPath('data.modes.0.id', $newMode->id);

    expect($user->fresh()->genrePreferences()->pluck('genres.id')->all())
        ->toBe([$newGenre->id])
        ->and($user->fresh()->modePreferences()->pluck('modes.id')->all())
        ->toBe([$newMode->id]);
});

it('validates preference payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/preferences', [
            'genre_ids' => [999],
            'mode_ids' => [888],
            'max_price' => -1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['genre_ids.0', 'mode_ids.0', 'max_price']);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/preferences', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['genre_ids', 'mode_ids']);
});

it('allows clearing max_price with null', function () {
    $user = User::factory()->create(['max_price' => 40]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/me/preferences', [
            'genre_ids' => [],
            'mode_ids' => [],
            'max_price' => null,
        ])
        ->assertOk()
        ->assertJsonPath('data.max_price', null);

    expect($user->fresh()->max_price)->toBeNull();
});
