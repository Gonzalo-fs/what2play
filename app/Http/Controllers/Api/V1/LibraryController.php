<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Library\IndexLibraryRequest;
use App\Http\Requests\Library\UpdateLibraryRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LibraryController extends Controller
{
    public function index(IndexLibraryRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();

        $games = $request->user()
            ->library()
            ->with(['developer', 'genres', 'modes'])
            ->when(array_key_exists('completed', $filters), function ($query) use ($filters): void {
                $query->filterByLibraryCompleted((bool) $filters['completed']);
            })
            ->when(array_key_exists('saved', $filters), function ($query) use ($filters): void {
                $query->filterByLibrarySaved((bool) $filters['saved']);
            })
            ->orderByPivot('updated_at', 'desc')
            ->paginate();

        return GameResource::collection($games);
    }

    public function update(UpdateLibraryRequest $request, Game $game): GameResource
    {
        $user = $request->user();
        $validated = $request->validated();

        $current = $user->library()->where('games.id', $game->id)->first();

        $pivot = [
            'completed' => array_key_exists('completed', $validated)
                ? (bool) $validated['completed']
                : (bool) ($current?->pivot->completed ?? false),
            'saved' => array_key_exists('saved', $validated)
                ? (bool) $validated['saved']
                : (bool) ($current?->pivot->saved ?? true),
        ];

        $user->library()->syncWithoutDetaching([
            $game->id => $pivot,
        ]);

        $libraryGame = $user->library()
            ->with(['developer', 'genres', 'modes'])
            ->where('games.id', $game->id)
            ->firstOrFail();

        return GameResource::make($libraryGame);
    }

    public function destroy(Request $request, Game $game): Response
    {
        $request->user()->library()->detach($game->id);

        return response()->noContent();
    }
}
