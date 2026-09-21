<?php

namespace App\Concerns;

trait TempatWisataValidationRules
{
    /** @return array<string, array<int, string>> */
    protected function tempatWisataRules(): array
    {
        $image = ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'];

        return [
            'destinasi_id' => ['required', 'integer', 'exists:destinasi,id'],
            'nama' => ['required', 'string', 'max:255'],
            'slug' => ['prohibited'],
            'deskripsi_singkat' => ['nullable', 'string', 'max:1000'],
            'deskripsi_lengkap' => ['nullable', 'string', 'max:50000'],
            'foto_utama' => ['nullable', ...$image],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'jam_operasional' => ['nullable', 'string', 'max:5000'],
            'harga_tiket' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'waktu_terbaik' => ['nullable', 'string', 'max:5000'],
            'tips' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'boolean'],
            'unggulan' => ['required', 'boolean'],
            'urutan' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'pengalaman' => ['sometimes', 'array', 'max:100'],
            'pengalaman.*' => ['required', 'integer', 'distinct', 'exists:pengalaman,id'],
            'aktivitas' => ['sometimes', 'array', 'max:100'],
            'aktivitas.*' => ['required', 'integer', 'distinct', 'exists:aktivitas,id'],
            'fasilitas' => ['sometimes', 'array', 'max:100'],
            'fasilitas.*' => ['required', 'integer', 'distinct', 'exists:fasilitas,id'],
            'galeri' => ['sometimes', 'array', 'max:10'],
            'galeri.*' => ['required', ...$image],
            'hapus_galeri' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'integer' => ':attribute harus berupa bilangan bulat.',
            'numeric' => ':attribute harus berupa angka.',
            'decimal' => ':attribute maksimal 2 angka desimal.',
            'boolean' => ':attribute harus bernilai aktif atau nonaktif.',
            'exists' => ':attribute tidak valid atau sudah dihapus.',
            'distinct' => ':attribute tidak boleh duplikat.',
            'array' => ':attribute harus berupa daftar.',
            'max.string' => ':attribute maksimal :max karakter.',
            'max.numeric' => ':attribute maksimal :max.',
            'min.numeric' => ':attribute minimal :min.',
            'max.array' => ':attribute maksimal :max pilihan/file per penyimpanan.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat JPEG, PNG, atau WebP.',
            'max.file' => ':attribute maksimal 5 MB.',
            'dimensions' => ':attribute maksimal 6000 × 6000 piksel.',
            'slug.prohibited' => 'Slug dibuat otomatis dari nama.',
            'hapus_galeri.prohibited' => 'Foto galeri hanya dapat dihapus saat edit.',
        ];
    }
}
