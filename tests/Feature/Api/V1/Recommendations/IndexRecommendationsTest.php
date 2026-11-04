<?php

use App\Models\Game;
use App\Models\Genre;
use App\Models\Mode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('rejects recommendations for guests', function () {
    $this->getJson('/api/v1/recommendations')
        ->assertUnauthorized();
});

it('ranks games by preference matches', function () {
    $user = User::factory()->create(['max_price' => null]);
    $action = Genre::factory()->create(['name' => 'Action']);
    $rpg = Genre::factory()->create(['name' => 'RPG']);
    $coop = Mode::factory()->create(['name' => 'Co-op']);

    $user->genrePreferences()->attach([$action->id, $rpg->id]);
    $user->modePreferences()->attach([$coop->id]);

    $best = Game::factory()->create(['slug' => 'best-match', 'score' => 5.0]);
    $mid = Game::factory()->create(['slug' => 'mid-match', 'score' => 9.0]);
    $low = Game::factory()->create(['slug' => 'low-match', 'score' => 9.5]);
    $none = Game::factory()->create(['slug' => 'no-match', 'score' => 10.0]);

    $best->genres()->attach([$action->id, $rpg->id]);
    $best->modes()->attach($coop);

    $mid->genres()->attach($action);
    $mid->modes()->attach($coop);

    $low->genres()->attach($action);

    $none->genres()->attach(Genre::factory()->create(['name' => 'Sports']));

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/recommendations');

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toBe(['best-match', 'mid-match', 'low-match'])
        ->and($slugs)->not->toContain('no-match');
});

it('excludes completed games but keeps saved ones', function () {
    $user = User::factory()->create(['max_price' => null]);
    $genre = Genre::factory()->create();
    $user->genrePreferences()->attach($genre);

    $completed = Game::factory()->create(['slug' => 'completed-game', 'score' => 10]);
    $saved = Game::factory()->create(['slug' => 'saved-game', 'score' => 8]);

    $completed->genres()->attach($genre);
    $saved->genres()->attach($genre);

    $user->library()->attach($completed->id, ['completed' => true, 'saved' => true]);
    $user->library()->attach($saved->id, ['completed' => false, 'saved' => true]);

    $slugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/recommendations')
            ->json('data')
    )->pluck('slug')->all();

    expect($slugs)->toContain('saved-game')
        ->and($slugs)->not->toContain('completed-game');
});

it('respects max_price and includes free games', function () {
    $user = User::factory()->create(['max_price' => 20]);
    $genre = Genre::factory()->create();
    $user->genrePreferences()->attach($genre);

    $cheap = Game::factory()->create(['slug' => 'cheap', 'price' => 10, 'score' => 7]);
    $free = Game::factory()->free()->create(['slug' => 'free', 'score' => 6]);
    $expensive = Game::factory()->create(['slug' => 'expensive', 'price' => 50, 'score' => 9]);

    $cheap->genres()->attach($genre);
    $free->genres()->attach($genre);
    $expensive->genres()->attach($genre);

    $slugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/recommendations')
            ->json('data')
    )->pluck('slug')->all();

    expect($slugs)->toContain('cheap', 'free')
        ->and($slugs)->not->toContain('expensive');
});

it('falls back to score ordering when the user has no preferences', function () {
    $user = User::factory()->create(['max_price' => null]);

    Game::factory()->create(['slug' => 'low', 'score' => 4.0]);
    Game::factory()->create(['slug' => 'high', 'score' => 9.0]);
    Game::factory()->create(['slug' => 'mid', 'score' => 7.0]);

    $slugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/recommendations')
            ->json('data')
    )->pluck('slug')->all();

    expect($slugs)->toBe(['high', 'mid', 'low']);
});

it('excludes soft deleted games', function () {
    $user = User::factory()->create(['max_price' => null]);
    $genre = Genre::factory()->create();
    $user->genrePreferences()->attach($genre);

    $visible = Game::factory()->create(['slug' => 'visible']);
    $deleted = Game::factory()->create(['slug' => 'deleted']);
    $visible->genres()->attach($genre);
    $deleted->genres()->attach($genre);
    $deleted->delete();

    $slugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/recommendations')
            ->json('data')
    )->pluck('slug')->all();

    expect($slugs)->toContain('visible')
        ->and($slugs)->not->toContain('deleted');
});

it('avoids n-plus-one queries when listing recommendations', function () {
    $user = User::factory()->create(['max_price' => null]);
    $genre = Genre::factory()->create();
    $mode = Mode::factory()->create();
    $user->genrePreferences()->attach($genre);
    $user->modePreferences()->attach($mode);

    $games = Game::factory()->count(5)->create();
    foreach ($games as $game) {
        $game->genres()->attach($genre);
        $game->modes()->attach($mode);
    }

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/recommendations')
        ->assertOk()
        ->assertJsonCount(5, 'data');

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(15);
});

it('ranks recommendations for a demo-shaped user with genres modes and max_price', function () {
    $user = User::factory()->create(['max_price' => 30]);
    $action = Genre::factory()->create(['name' => 'Action']);
    $rpg = Genre::factory()->create(['name' => 'RPG']);
    $single = Mode::factory()->create(['name' => 'Single player']);

    $user->genrePreferences()->attach([$action->id, $rpg->id]);
    $user->modePreferences()->attach($single);

    $best = Game::factory()->create(['slug' => 'demo-best', 'score' => 6.0, 'price' => 19.99]);
    $mid = Game::factory()->create(['slug' => 'demo-mid', 'score' => 9.0, 'price' => 25.00]);
    $free = Game::factory()->free()->create(['slug' => 'demo-free', 'score' => 5.0]);
    $expensive = Game::factory()->create(['slug' => 'demo-expensive', 'score' => 10.0, 'price' => 59.99]);
    $unrelated = Game::factory()->create(['slug' => 'demo-unrelated', 'score' => 10.0, 'price' => 10.00]);

    $best->genres()->attach([$action->id, $rpg->id]);
    $best->modes()->attach($single);

    $mid->genres()->attach($action);
    $mid->modes()->attach($single);

    $free->genres()->attach($action);

    $expensive->genres()->attach([$action->id, $rpg->id]);
    $expensive->modes()->attach($single);

    $unrelated->genres()->attach(Genre::factory()->create(['name' => 'Sports']));

    $slugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/recommendations')
            ->assertOk()
            ->json('data')
    )->pluck('slug')->all();

    expect($slugs)->toBe(['demo-best', 'demo-mid', 'demo-free'])
        ->and($slugs)->not->toContain('demo-expensive', 'demo-unrelated');
});
