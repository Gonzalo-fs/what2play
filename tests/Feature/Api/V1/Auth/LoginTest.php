<?php

use App\Models\User;

it('logs in a user and returns a token', function () {
    $user = User::factory()->create([
        'email' => 'gonzalo@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'gonzalo@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'is_admin', 'max_price'],
            ],
        ])
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', 'gonzalo@example.com');

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
});

it('rejects wrong credentials with the same message', function (string $email, string $password) {
    User::factory()->create([
        'email' => 'gonzalo@example.com',
        'password' => 'password',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $email,
        'password' => $password,
    ])->assertUnauthorized()
        ->assertJson(['message' => 'Invalid credentials.']);
})->with([
    'wrong password' => ['gonzalo@example.com', 'wrong-password'],
    'unknown email' => ['unknown@example.com', 'password'],
]);

it('validates login fields', function () {
    $this->postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('throttles login attempts', function () {
    User::factory()->create([
        'email' => 'throttle@example.com',
        'password' => 'password',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => 'throttle@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => 'throttle@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(429);
});
