<?php

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Mode;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('seeds the real catalog, demo users, and ranked recommendations', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Developer::query()->count())->toBe(20)
        ->and(Genre::query()->count())->toBe(13)
        ->and(Mode::query()->count())->toBe(3)
        ->and(Game::query()->count())->toBe(22);

    $hades = Game::query()->where('slug', 'hades')->with(['genres', 'modes', 'developer'])->firstOrFail();
    expect($hades->developer->name)->toBe('Supergiant Games')
        ->and($hades->genres->pluck('name')->sort()->values()->all())->toBe(['action and adventure', 'arcade'])
        ->and($hades->modes->pluck('name')->all())->toBe(['Solo']);

    $hellLetLoose = Game::query()->where('slug', 'hell-let-loose')->with(['genres', 'modes'])->firstOrFail();
    expect($hellLetLoose->genres->pluck('name')->sort()->values()->all())
        ->toBe(['management and strategy', 'shooter'])
        ->and($hellLetLoose->modes->pluck('name')->sort()->values()->all())
        ->toBe(['Against other players', 'With friends']);

    $witcher = Game::query()->where('slug', 'the-witcher-3-wild-hunt')->with(['genres', 'modes'])->firstOrFail();
    expect($witcher->genres->pluck('name')->sort()->values()->all())
        ->toBe(['action and adventure', 'sandbox', 'story based'])
        ->and($witcher->modes->pluck('name')->all())->toBe(['Solo']);

    $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
    expect($admin->is_admin)->toBeTrue();

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();
    expect((float) $user->max_price)->toBe(30.0)
        ->and($user->genrePreferences->pluck('name')->sort()->values()->all())
        ->toBe(['action and adventure', 'story based'])
        ->and($user->modePreferences->pluck('name')->all())->toBe(['Solo']);

    $library = $user->library()->withPivot(['saved', 'completed'])->get()->keyBy('slug');
    expect($library)->toHaveCount(2)
        ->and((bool) $library['hades']->pivot->saved)->toBeTrue()
        ->and((bool) $library['hades']->pivot->completed)->toBeTrue()
        ->and((bool) $library['the-witcher-3-wild-hunt']->pivot->saved)->toBeTrue()
        ->and((bool) $library['the-witcher-3-wild-hunt']->pivot->completed)->toBeFalse();

    $slugs = collect(
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/recommendations')
            ->assertOk()
            ->json('data')
    )->pluck('slug')->all();

    expect($slugs)->toBe([
        'the-witcher-3-wild-hunt',
        'portal-2',
        'celeste',
        'stardew-valley',
        'cities-skylines',
        'cuphead',
        'snowrunner',
    ])
        ->and($slugs)->not->toContain('hades', 'cyberpunk-2077', 'elden-ring', 'days-gone');
});
