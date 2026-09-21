<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JelajahiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'destinasi_id' => ['nullable', 'integer', 'exists:destinasi,id'],
            'pengalaman_id' => ['nullable', 'integer', 'exists:pengalaman,id'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'search.max' => 'Pencarian maksimal 255 karakter.',
            'search.string' => 'Pencarian harus berupa teks.',
            'exists' => 'Pilihan filter tidak tersedia.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'page.min' => 'Halaman minimal 1.',
        ];
    }
}
