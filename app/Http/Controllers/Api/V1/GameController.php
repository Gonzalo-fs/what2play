<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Games\IndexGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use App\Services\GameFilterService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GameController extends Controller
{
    public function index(IndexGameRequest $request, GameFilterService $gameFilterService): AnonymousResourceCollection
    {
        $filters = $request->validated();

        return GameResource::collection(
            $gameFilterService->filter(
                $filters['genre_ids'] ?? [],
                $filters['mode_ids'] ?? [],
                isset($filters['max_price']) ? (float) $filters['max_price'] : null,
                strict: true,
            )
        );
    }

    public function show(Game $game): GameResource
    {
        $game->loadMissing(['developer', 'genres', 'modes']);

        return GameResource::make($game);
    }
}
