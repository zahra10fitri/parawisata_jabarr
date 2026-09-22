<?php

namespace App\Concerns;

trait ArtikelValidationRules
{
    /** @return array<string, array<int, string>> */
    protected function artikelRules(): array
    {
        return ['kategori_artikel_id' => ['required', 'integer', 'exists:kategori_artikel,id'], 'judul' => ['required', 'string', 'max:255'], 'slug' => ['prohibited'], 'ringkasan' => ['nullable', 'string', 'max:1000'], 'konten' => ['required', 'string', 'max:100000'], 'foto_utama' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'], 'status' => ['required', 'boolean'], 'unggulan' => ['required', 'boolean'], 'tanggal_publikasi' => ['nullable', 'date_format:Y-m-d\TH:i'], 'destinasi' => ['sometimes', 'array', 'max:50'], 'destinasi.*' => ['required', 'integer', 'distinct', 'exists:destinasi,id'], 'tempat_wisata' => ['sometimes', 'array', 'max:100'], 'tempat_wisata.*' => ['required', 'integer', 'distinct', 'exists:tempat_wisata,id']];
    }
}
