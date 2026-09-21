<?php

namespace App\Http\Requests\Admin;

use App\Concerns\TempatWisataValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreTempatWisataRequest extends FormRequest
{
    use TempatWisataValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') === true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return $this->tempatWisataRules();
    }
}
