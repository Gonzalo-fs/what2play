<?php

it('returns a JSON 401 for guests even without an Accept header', function () {
    $this->get('/api/v1/me')
        ->assertUnauthorized()
        ->assertJson(['message' => 'Unauthenticated.']);
});
