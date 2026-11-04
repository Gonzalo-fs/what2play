<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecommendationService
{
    /**
     * Recommend games for a user.
     *
     * Candidates are non-deleted games the user has not marked completed, optionally
     * filtered by the user's max_price (null price = free, always allowed; no filter
     * when max_price is null). Relevance is the count of the user's preferred genres
     * plus preferred modes that the game matches. Only games with relevance > 0 are
     * returned when preferences exist. With no preferences, results fall back to the
     * catalog score ordering. Final order: relevance desc, then score desc (nulls last), then id desc.
     *
     * @return LengthAwarePaginator<int, Game>
     */
    public function paginateFor(User $user): LengthAwarePaginator
    {
        $genreIds = $user->genrePreferences()->pluck('genres.id')->all();
        $modeIds = $user->modePreferences()->pluck('modes.id')->all();

        $query = Game::query()
            ->with(['developer', 'genres', 'modes'])
            ->notCompletedBy($user);

        if ($user->max_price !== null) {
            $query->filterByMaxPrice($user->max_price);
        }

        if ($genreIds !== [] || $modeIds !== []) {
            $query->withPreferenceRelevance($genreIds, $modeIds);
        }

        return $query->orderByScoreDesc()->paginate();
    }
}
