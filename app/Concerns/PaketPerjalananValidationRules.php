<?php

namespace App\Concerns;

trait PaketPerjalananValidationRules
{
    /** @return array<string, array<int, string>> */
    protected function paketPerjalananRules(): array
    {
        return ['nama' => ['required', 'string', 'max:255'], 'slug' => ['prohibited'], 'deskripsi' => ['nullable', 'string', 'max:50000'], 'foto_utama' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'durasi_hari' => ['required', 'integer', 'min:1', 'max:365'], 'harga_mulai' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'], 'highlight' => ['nullable', 'string', 'max:10000'], 'termasuk' => ['nullable', 'string', 'max:10000'], 'tidak_termasuk' => ['nullable', 'string', 'max:10000'], 'status' => ['required', 'boolean'], 'unggulan' => ['required', 'boolean'], 'urutan' => ['required', 'integer', 'min:0', 'max:4294967295'], 'destinasi' => ['sometimes', 'array', 'max:50'], 'destinasi.*' => ['required', 'integer', 'distinct', 'exists:destinasi,id'], 'tempat_wisata' => ['sometimes', 'array', 'max:100'], 'tempat_wisata.*' => ['required', 'integer', 'distinct', 'exists:tempat_wisata,id']];
    }
}
