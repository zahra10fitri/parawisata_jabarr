<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePesanKontakRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s-]{7,28}$/'],
            'subjek' => ['required', 'string', 'max:255'],
            'pesan' => ['required', 'string', 'max:10000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['nama', 'email', 'whatsapp', 'subjek', 'pesan'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field)) ?: null]);
            }
        }
    }
}
