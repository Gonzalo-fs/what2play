<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\GameWriteRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreGameRequest extends FormRequest
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
        return $this->gameRules();
    }
}
