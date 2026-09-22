<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexPesanKontakRequest extends FormRequest
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
        return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:belum_dibaca,sudah_dibaca']];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('search'))) {
            $this->merge(['search' => trim($this->input('search'))]);
        }
    }
}
