<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GameFilterService
{
    /**
     * Games within the price limit, best first.
     *
     * By default games are ranked by how many of the given genres and modes they match,
     * then by score. In strict mode they must match one of the genres and one of the modes,
     * and are ordered by score. When a user is given, games they completed are left out.
     *
     * @param  list<int>  $genreIds
     * @param  list<int>  $modeIds
     * @return LengthAwarePaginator<int, Game>
     */
    public function filter(
        array $genreIds,
        array $modeIds,
        ?float $maxPrice = null,
        ?User $user = null,
        bool $strict = false,
    ): LengthAwarePaginator {
        $query = Game::query()
            ->with(['developer', 'genres', 'modes'])
            ->filterByMaxPrice($maxPrice);

        if ($user !== null) {
            $query->notCompletedBy($user);
        }

        if ($strict) {
            $query->filterByGenreIds($genreIds)->filterByModeIds($modeIds);
        } elseif ($genreIds !== [] || $modeIds !== []) {
            $query->withPreferenceRelevance($genreIds, $modeIds);
        }

        return $query->orderByScoreDesc()->paginate();
    }
}
