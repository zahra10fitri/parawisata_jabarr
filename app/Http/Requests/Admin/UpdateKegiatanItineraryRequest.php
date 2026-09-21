<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKegiatanItineraryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['judul' => ['required', 'string', 'max:255'], 'keterangan' => ['nullable', 'string', 'max:10000'], 'waktu' => ['nullable', 'date_format:H:i'], 'urutan' => ['required', 'integer', 'min:0', 'max:4294967295']];
    }
}
