<?php

use App\Models\User;

it('rejects requests with an expired sanctum token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('api')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertOk();

    $this->travel((int) config('sanctum.expiration') + 1)->minutes();

    $this->app['auth']->forgetGuards();

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});
