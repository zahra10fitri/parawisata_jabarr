<?php

namespace Database\Factories;

use App\Models\PaketPerjalanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PaketPerjalanan>
 */
class PaketPerjalananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake()->unique()->sentence(3);

        return ['nama' => $nama, 'slug' => Str::slug($nama), 'durasi_hari' => 3, 'harga_mulai' => 500000, 'status' => false, 'unggulan' => false, 'urutan' => 0];
    }
}
