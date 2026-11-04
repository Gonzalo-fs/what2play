<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class PreferencesResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'genres' => GenreResource::collection($this->whenLoaded('genrePreferences')),
            'modes' => ModeResource::collection($this->whenLoaded('modePreferences')),
            'max_price' => $this->max_price,
        ];
    }
}
