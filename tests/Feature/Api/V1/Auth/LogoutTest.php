<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

it('logs out the authenticated user and revokes the current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api');
    $plainTextToken = $token->plainTextToken;
    $tokenId = $token->accessToken->id;

    $this->withToken($plainTextToken)
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    expect(PersonalAccessToken::query()->find($tokenId))->toBeNull();

    $this->app['auth']->forgetGuards();

    $this->withToken($plainTextToken)
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});

it('rejects logout for guests', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthenticated.']);
});
