<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexDestinasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') === true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
