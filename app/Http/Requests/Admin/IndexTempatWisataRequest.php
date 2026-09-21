<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class IndexTempatWisataRequest extends FormRequest
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
            'destinasi_id' => ['nullable', 'integer', 'exists:destinasi,id'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
