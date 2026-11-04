<?php

use App\Models\Game;
use App\Models\Genre;
use App\Models\Mode;
use Illuminate\Support\Facades\DB;

it('lists games paginated and ordered by score descending', function () {
    $low = Game::factory()->create(['score' => 5.0, 'slug' => 'low-score']);
    $high = Game::factory()->create(['score' => 9.5, 'slug' => 'high-score']);
    $mid = Game::factory()->create(['score' => 7.0, 'slug' => 'mid-score']);
    Game::factory()->create(['score' => null, 'slug' => 'no-score']);

    $response = $this->getJson('/api/v1/games');

    $response->assertOk()
        ->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toBe(['high-score', 'mid-score', 'low-score', 'no-score']);
});

it('filters games by genre ids with or within the group', function () {
    $action = Genre::factory()->create(['name' => 'Action']);
    $rpg = Genre::factory()->create(['name' => 'RPG']);
    $puzzle = Genre::factory()->create(['name' => 'Puzzle']);

    $actionGame = Game::factory()->create(['slug' => 'action-game']);
    $rpgGame = Game::factory()->create(['slug' => 'rpg-game']);
    $puzzleGame = Game::factory()->create(['slug' => 'puzzle-game']);

    $actionGame->genres()->attach($action);
    $rpgGame->genres()->attach($rpg);
    $puzzleGame->genres()->attach($puzzle);

    $response = $this->getJson('/api/v1/games?genre_ids[]='.$action->id.'&genre_ids[]='.$rpg->id);

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toContain('action-game', 'rpg-game')
        ->and($slugs)->not->toContain('puzzle-game');
});

it('filters games by mode ids', function () {
    $coop = Mode::factory()->create(['name' => 'Co-op']);
    $solo = Mode::factory()->create(['name' => 'Single player']);

    $coopGame = Game::factory()->create(['slug' => 'coop-game']);
    $soloGame = Game::factory()->create(['slug' => 'solo-game']);

    $coopGame->modes()->attach($coop);
    $soloGame->modes()->attach($solo);

    $response = $this->getJson('/api/v1/games?mode_ids[]='.$coop->id);

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toContain('coop-game')
        ->and($slugs)->not->toContain('solo-game');
});

it('applies genre and mode filters together with and across groups', function () {
    $action = Genre::factory()->create(['name' => 'Action']);
    $rpg = Genre::factory()->create(['name' => 'RPG']);
    $coop = Mode::factory()->create(['name' => 'Co-op']);
    $solo = Mode::factory()->create(['name' => 'Single player']);

    $match = Game::factory()->create(['slug' => 'match']);
    $wrongMode = Game::factory()->create(['slug' => 'wrong-mode']);
    $wrongGenre = Game::factory()->create(['slug' => 'wrong-genre']);

    $match->genres()->attach($action);
    $match->modes()->attach($coop);

    $wrongMode->genres()->attach($action);
    $wrongMode->modes()->attach($solo);

    $wrongGenre->genres()->attach($rpg);
    $wrongGenre->modes()->attach($coop);

    $response = $this->getJson('/api/v1/games?genre_ids[]='.$action->id.'&mode_ids[]='.$coop->id);

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toBe(['match']);
});

it('filters by max price and includes free games', function () {
    Game::factory()->create(['slug' => 'cheap', 'price' => 10.00]);
    Game::factory()->create(['slug' => 'expensive', 'price' => 50.00]);
    Game::factory()->free()->create(['slug' => 'free']);

    $response = $this->getJson('/api/v1/games?max_price=20');

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toContain('cheap', 'free')
        ->and($slugs)->not->toContain('expensive');
});

it('excludes soft deleted games from the list', function () {
    Game::factory()->create(['slug' => 'visible']);
    Game::factory()->create(['slug' => 'gone'])->delete();

    $response = $this->getJson('/api/v1/games');

    $response->assertOk();

    $slugs = collect($response->json('data'))->pluck('slug')->all();

    expect($slugs)->toContain('visible')
        ->and($slugs)->not->toContain('gone');
});

it('rejects invalid genre and mode filter ids', function () {
    $this->getJson('/api/v1/games?genre_ids[]=999&mode_ids[]=888')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['genre_ids.0', 'mode_ids.0']);
});

it('avoids n-plus-one queries when listing games', function () {
    Game::factory()->count(5)->create()->each(function (Game $game): void {
        $game->genres()->attach(Genre::factory()->create());
        $game->modes()->attach(Mode::factory()->create());
    });

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->getJson('/api/v1/games')
        ->assertOk()
        ->assertJsonCount(5, 'data');

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(15);
});
