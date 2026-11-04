<?php

use App\Models\Genre;

it('lists genres ordered by name without authentication', function () {
    Genre::factory()->create(['name' => 'Zebra', 'description' => 'Last alphabetically']);
    Genre::factory()->create(['name' => 'Action', 'description' => 'First alphabetically']);
    Genre::factory()->create(['name' => 'Puzzle', 'description' => 'Middle alphabetically']);

    $response = $this->getJson('/api/v1/genres');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description'],
            ],
        ]);

    $names = collect($response->json('data'))->pluck('name')->all();

    expect($names)->toBe(['Action', 'Puzzle', 'Zebra']);
});
