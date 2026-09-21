<?php

namespace App\Http\Requests\Admin;

use App\Models\TempatWisata;
use Illuminate\Validation\Rule;

class UpdateTempatWisataRequest extends StoreTempatWisataRequest
{
    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $tempatWisata = $this->route('tempat_wisata');

        return [
            ...$this->tempatWisataRules(),
            'hapus_galeri' => ['sometimes', 'array', 'max:20'],
            'hapus_galeri.*' => [
                'required', 'integer', 'distinct',
                Rule::exists('galeri_tempat_wisata', 'id')->where('tempat_wisata_id', $tempatWisata instanceof TempatWisata ? $tempatWisata->id : null),
            ],
        ];
    }
}
