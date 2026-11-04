<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreGameRequest;
use App\Http\Requests\Admin\UpdateGameImageRequest;
use App\Http\Requests\Admin\UpdateGameRequest;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminGameController extends Controller
{
    public function store(StoreGameRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['genre_ids', 'mode_ids']);
        $genreIds = $request->validated('genre_ids');
        $modeIds = $request->validated('mode_ids');

        $slug = $data['slug'] ?? null;
        if (blank($slug)) {
            $data['slug'] = Game::generateUniqueSlug(Str::slug((string) $data['title']));
        }

        $game = DB::transaction(function () use ($data, $genreIds, $modeIds): Game {
            $game = Game::query()->create($data);
            $game->genres()->sync($genreIds);
            $game->modes()->sync($modeIds);

            return $game;
        });

        $game->load(['developer', 'genres', 'modes']);

        return GameResource::make($game)
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateGameRequest $request, Game $game): GameResource
    {
        $data = $request->safe()->except(['genre_ids', 'mode_ids']);
        $validated = $request->validated();

        DB::transaction(function () use ($game, $data, $validated): void {
            if ($data !== []) {
                $game->update($data);
            }

            if (array_key_exists('genre_ids', $validated)) {
                $game->genres()->sync($validated['genre_ids']);
            }

            if (array_key_exists('mode_ids', $validated)) {
                $game->modes()->sync($validated['mode_ids']);
            }
        });

        $game->load(['developer', 'genres', 'modes']);

        return GameResource::make($game);
    }

    public function destroy(Game $game): Response
    {
        $this->authorize('delete', $game);

        $game->delete();

        return response()->noContent();
    }

    public function updateImage(UpdateGameImageRequest $request, Game $game): GameResource
    {
        $path = $request->file('image')->store('games', 'public');
        $previous = $game->image_path;

        $game->update(['image_path' => $path]);

        if ($previous !== null && $previous !== $path) {
            Storage::disk('public')->delete($previous);
        }

        $game->load(['developer', 'genres', 'modes']);

        return GameResource::make($game);
    }
}
