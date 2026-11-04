<?php

use App\Models\User;

it('returns the authenticated user', function () {
    $user = User::factory()->create([
        'name' => 'Gonzalo',
        'email' => 'gonzalo@example.com',
        'max_price' => 40.50,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', 'Gonzalo')
        ->assertJsonPath('data.email', 'gonzalo@example.com')
        ->assertJsonPath('data.is_admin', false)
        ->assertJsonPath('data.max_price', '40.50');
});
