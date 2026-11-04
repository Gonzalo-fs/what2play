<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\GameWriteRules;
use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGameRequest extends FormRequest
{
    use GameWriteRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Game $game */
        $game = $this->route('game');

        return $this->gameRules($game, partial: true);
    }
}
