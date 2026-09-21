<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaketPerjalananRequest extends FormRequest
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
        return ['destinasi_id' => ['nullable', 'integer', 'exists:destinasi,id'], 'durasi' => ['nullable', 'integer', 'min:1', 'max:365'], 'pengalaman_id' => ['nullable', 'integer', 'exists:pengalaman,id'], 'page' => ['nullable', 'integer', 'min:1']];
    }
}
