<?php

namespace App\Concerns;

trait DestinasiValidationRules
{
    /** @return array<string, array<int, string>> */
    protected function destinasiRules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'slug' => ['prohibited'],
            'deskripsi_singkat' => ['nullable', 'string', 'max:1000'],
            'deskripsi_lengkap' => ['nullable', 'string', 'max:50000'],
            'foto_utama' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'waktu_terbaik' => ['nullable', 'string', 'max:5000'],
            'cara_menuju' => ['nullable', 'string', 'max:5000'],
            'transportasi_lokal' => ['nullable', 'string', 'max:5000'],
            'tips' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'boolean'],
            'unggulan' => ['required', 'boolean'],
            'urutan' => ['required', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'max.string' => ':attribute maksimal :max karakter.',
            'boolean' => ':attribute harus bernilai aktif atau nonaktif.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'min.numeric' => ':attribute minimal :min.',
            'max.numeric' => ':attribute maksimal :max.',
            'slug.prohibited' => 'Slug dibuat otomatis dari nama.',
            'foto_utama.image' => 'Foto utama harus berupa gambar.',
            'foto_utama.mimes' => 'Foto utama harus berformat JPEG, PNG, atau WebP.',
            'foto_utama.max' => 'Foto utama maksimal 5 MB.',
            'foto_utama.dimensions' => 'Dimensi foto utama maksimal 6000 × 6000 piksel.',
        ];
    }
}
