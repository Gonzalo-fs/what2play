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

        $query = $request->user()
            ->library()
            ->with(['developer', 'genres', 'modes']);

        if (array_key_exists('completed', $filters)) {
            $query->wherePivot('completed', (int) filter_var($filters['completed'], FILTER_VALIDATE_BOOLEAN));
        }

        if (array_key_exists('saved', $filters)) {
            $query->wherePivot('saved', (int) filter_var($filters['saved'], FILTER_VALIDATE_BOOLEAN));
        }

        $games = $query
            ->orderByPivot('updated_at', 'desc')
            ->paginate();

        return GameResource::collection($games);
    }

    public function update(UpdateLibraryRequest $request, Game $game): GameResource
    {
        $user = $request->user();
        $validated = $request->validated();

        $inLibrary = $user->library()->where('games.id', $game->id)->exists();

        if ($inLibrary) {
            $flags = array_intersect_key($validated, array_flip(['completed', 'saved']));
            if ($flags !== []) {
                $user->library()->updateExistingPivot($game->id, $flags);
            }
        } else {
            $user->library()->attach($game->id, [
                'completed' => $validated['completed'] ?? false,
                'saved' => $validated['saved'] ?? true,
            ]);
        }

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
