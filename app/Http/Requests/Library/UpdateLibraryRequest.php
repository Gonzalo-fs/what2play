<?php

namespace App\Http\Requests\Library;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLibraryRequest extends FormRequest
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
            'completed' => ['sometimes', 'boolean'],
            'saved' => ['sometimes', 'boolean'],
        ];
    }
}
