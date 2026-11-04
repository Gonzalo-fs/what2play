<?php

namespace App\Http\Requests\Games;

use Illuminate\Foundation\Http\FormRequest;

class IndexGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'genre_ids' => ['sometimes', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'mode_ids' => ['sometimes', 'array'],
            'mode_ids.*' => ['integer', 'exists:modes,id'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
