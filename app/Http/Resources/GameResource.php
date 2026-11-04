<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Game
 */
class GameResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'release_year' => $this->release_year,
            'disclaimer' => $this->disclaimer,
            'video_url' => $this->video_url,
            'price' => $this->price,
            'buy_url' => $this->buy_url,
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
            'difficulty' => $this->difficulty,
            'audience' => $this->audience,
            'graphics' => $this->graphics,
            'requirements' => $this->requirements,
            'requirements_specs' => $this->requirements_specs,
            'game_length_minutes' => $this->game_length_minutes,
            'score' => $this->score,
            'completed' => $this->whenPivotLoaded('game_user', function (): bool {
                return (bool) $this->resource->getRelation('pivot')->getAttribute('completed');
            }),
            'saved' => $this->whenPivotLoaded('game_user', function (): bool {
                return (bool) $this->resource->getRelation('pivot')->getAttribute('saved');
            }),
            'developer' => DeveloperResource::make($this->whenLoaded('developer')),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'modes' => ModeResource::collection($this->whenLoaded('modes')),
        ];
    }
}
