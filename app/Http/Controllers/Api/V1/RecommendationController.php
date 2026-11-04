<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RecommendationController extends Controller
{
    public function index(Request $request, RecommendationService $recommendationService): AnonymousResourceCollection
    {
        return GameResource::collection(
            $recommendationService->paginateFor($request->user())
        );
    }
}
