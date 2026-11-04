<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecommendationService
{
    /**
     * Games the user has not completed and can afford, ranked by how many of their
     * preferred genres and modes they match, then by score.
     *
     * @return LengthAwarePaginator<int, Game>
     */
    public function paginateFor(User $user): LengthAwarePaginator
    {
        $genreIds = $user->genrePreferences()->pluck('genres.id')->all();
        $modeIds = $user->modePreferences()->pluck('modes.id')->all();

        $query = Game::query()
            ->with(['developer', 'genres', 'modes'])
            ->notCompletedBy($user)
            ->filterByMaxPrice($user->max_price);

        if ($genreIds !== [] || $modeIds !== []) {
            $query->withPreferenceRelevance($genreIds, $modeIds);
        }

        return $query->orderByScoreDesc()->paginate();
    }
}
