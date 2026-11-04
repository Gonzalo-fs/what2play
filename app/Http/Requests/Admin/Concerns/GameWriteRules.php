<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\Game;
use Illuminate\Validation\Rule;

trait GameWriteRules
{
    /**
     * @return array<string, mixed>
     */
    protected function gameRules(?Game $game = null, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';
        $present = $partial ? 'sometimes' : 'present';

        $slugUnique = Rule::unique('games', 'slug');
        if ($game !== null) {
            $slugUnique = $slugUnique->ignore($game->id);
        }

        return [
            'title' => [$required, 'string', 'max:255'],
            'slug' => [$partial ? 'sometimes' : 'nullable', 'string', 'max:255', $slugUnique],
            'description' => [$required, 'string'],
            'release_year' => ['nullable', 'integer', 'min:1970', 'max:2100'],
            'disclaimer' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'buy_url' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['nullable', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'graphics' => ['nullable', 'string', 'max:255'],
            'requirements' => ['nullable', 'string'],
            'requirements_specs' => ['nullable', 'string'],
            'game_length_minutes' => ['nullable', 'integer', 'min:0'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'developer_id' => [$required, 'integer', 'exists:developers,id'],
            'genre_ids' => [$present, 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'mode_ids' => [$present, 'array'],
            'mode_ids.*' => ['integer', 'exists:modes,id'],
        ];
    }
}
