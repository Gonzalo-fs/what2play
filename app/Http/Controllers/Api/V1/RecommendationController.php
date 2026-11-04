<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Services\GameFilterService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecommendationController extends Controller
{
    public function index(Request $request, GameFilterService $gameFilterService): AnonymousResourceCollection
    {
        $user = $request->user();

        return GameResource::collection(
            $gameFilterService->filter(
                $user->genrePreferences()->pluck('genres.id')->all(),
                $user->modePreferences()->pluck('modes.id')->all(),
                $user->max_price !== null ? (float) $user->max_price : null,
                $user,
            )
        );
    }
}
