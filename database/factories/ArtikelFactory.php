<?php

namespace Database\Factories;

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Artikel>
 */
class ArtikelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $judul = fake()->unique()->sentence(5);

        return ['kategori_artikel_id' => KategoriArtikel::factory(), 'judul' => $judul, 'slug' => Str::slug($judul), 'ringkasan' => fake()->sentence(), 'konten' => fake()->paragraphs(3, true), 'status' => false, 'unggulan' => false, 'tanggal_publikasi' => null];
    }
}
