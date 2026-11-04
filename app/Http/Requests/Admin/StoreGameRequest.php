<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\GameWriteRules;
use App\Models\Game;
use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
{
    use GameWriteRules;

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Game::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->gameRules();
    }
}
