<?php

use App\Models\Mode;

it('lists modes ordered by name without authentication', function () {
    Mode::factory()->create(['name' => 'With friends', 'description' => 'Co-op']);
    Mode::factory()->create(['name' => 'Against other players', 'description' => 'Competitive']);
    Mode::factory()->create(['name' => 'Solo', 'description' => 'Singleplayer']);

    $response = $this->getJson('/api/v1/modes');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description'],
            ],
        ]);

    $names = collect($response->json('data'))->pluck('name')->all();

    expect($names)->toBe(['Against other players', 'Solo', 'With friends']);
});
