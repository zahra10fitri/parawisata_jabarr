<?php

namespace Database\Factories;

use App\Models\KategoriArtikel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KategoriArtikel>
 */
class KategoriArtikelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake()->unique()->words(2, true);

        return ['nama' => $nama, 'slug' => Str::slug($nama)];
    }
}
