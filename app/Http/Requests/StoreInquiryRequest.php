<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInquiryRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'paket_perjalanan_id' => ['nullable', 'integer', Rule::exists('paket_perjalanan', 'id')->where('status', true)],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'whatsapp' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s-]{7,28}$/'],
            'tanggal_perjalanan' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'jumlah_traveler' => ['required', 'integer', 'min:1', 'max:1000'],
            'catatan' => ['nullable', 'string', 'max:10000'],
            'destinasi' => ['sometimes', 'array', 'max:50'],
            'destinasi.*' => ['required', 'integer', 'distinct', Rule::exists('destinasi', 'id')->where('status', true)],
            'pengalaman' => ['sometimes', 'array', 'max:50'],
            'pengalaman.*' => ['required', 'integer', 'distinct', Rule::exists('pengalaman', 'id')->where('status', true)],
            'status' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['nama', 'email', 'whatsapp', 'catatan'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field)) ?: null]);
            }
        }
    }
}
