<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
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
            'genre_ids' => ['present', 'array'],
            'genre_ids.*' => ['integer', 'exists:genres,id'],
            'mode_ids' => ['present', 'array'],
            'mode_ids.*' => ['integer', 'exists:modes,id'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
