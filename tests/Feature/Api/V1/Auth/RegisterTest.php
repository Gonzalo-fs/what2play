<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('registers a user and returns a token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Gonzalo',
        'email' => 'gonzalo@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'is_admin', 'max_price'],
            ],
        ])
        ->assertJsonPath('data.user.name', 'Gonzalo')
        ->assertJsonPath('data.user.email', 'gonzalo@example.com')
        ->assertJsonPath('data.user.is_admin', false)
        ->assertJsonPath('data.user.max_price', null);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();

    $user = User::query()->where('email', 'gonzalo@example.com')->first();

    expect($user)->not->toBeNull()
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

it('requires all registration fields', function () {
    $this->postJson('/api/v1/auth/register', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

it('rejects an invalid email on register', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Gonzalo',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('rejects a short password on register', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Gonzalo',
        'email' => 'gonzalo@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('rejects an unconfirmed password on register', function () {
    $this->postJson('/api/v1/auth/register', [
        'name' => 'Gonzalo',
        'email' => 'gonzalo@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('rejects a duplicate email on register', function () {
    User::factory()->create(['email' => 'gonzalo@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name' => 'Gonzalo',
        'email' => 'gonzalo@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
