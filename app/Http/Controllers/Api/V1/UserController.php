<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdatePreferencesRequest;
use App\Http\Resources\PreferencesResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function show(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }

    public function showPreferences(Request $request): PreferencesResource
    {
        $user = $request->user()->load(['genrePreferences', 'modePreferences']);

        return PreferencesResource::make($user);
    }

    public function updatePreferences(UpdatePreferencesRequest $request): PreferencesResource
    {
        $user = $request->user();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data): void {
            $user->genrePreferences()->sync($data['genre_ids']);
            $user->modePreferences()->sync($data['mode_ids']);
            $user->update(['max_price' => $data['max_price'] ?? null]);
        });

        $user->load(['genrePreferences', 'modePreferences']);

        return PreferencesResource::make($user);
    }
}
