<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;

class GamePolicy
{
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, Game $game): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, Game $game): bool
    {
        return $user->is_admin;
    }
}
