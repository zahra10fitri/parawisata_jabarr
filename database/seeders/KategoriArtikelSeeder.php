<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KategoriArtikelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Panduan Perjalanan', 'Itinerary', 'Budaya', 'Kuliner', 'Hidden Gem', 'Tips Perjalanan'] as $nama) {
            DB::table('kategori_artikel')->updateOrInsert(['slug' => Str::slug($nama)], ['nama' => $nama, 'updated_at' => now(), 'created_at' => now()]);
        }
    }
}
