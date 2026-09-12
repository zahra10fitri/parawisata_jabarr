<?php

namespace App\Http\Requests\Admin;

use App\Concerns\DestinasiValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreDestinasiRequest extends FormRequest
{
    use DestinasiValidationRules;

    public function authorize(): bool
    {
        return $this->user()?->can('access-admin') === true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return $this->destinasiRules();
    }
}
