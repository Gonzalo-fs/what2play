<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Games\IndexGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GameController extends Controller
{
    public function index(IndexGameRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $games = Game::query()
            ->with(['developer', 'genres', 'modes'])
            ->filterByGenreIds($filters['genre_ids'] ?? null)
            ->filterByModeIds($filters['mode_ids'] ?? null)
            ->filterByMaxPrice($filters['max_price'] ?? null)
            ->orderByScoreDesc()
            ->paginate();

        return GameResource::collection($games);
    }

    public function show(Game $game): GameResource
    {
        $game->loadMissing(['developer', 'genres', 'modes']);

        return GameResource::make($game);
    }
}
