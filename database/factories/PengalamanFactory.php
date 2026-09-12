<?php

namespace Database\Factories;

use App\Models\Pengalaman;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pengalaman>
 */
class PengalamanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nama = fake()->unique()->sentence(3);

        return [
            'nama' => $nama,
            'slug' => Str::slug($nama),
        ];
    }

    /**
     * Indicate that the master record is active.
     */
    public function aktif(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => true,
        ]);
    }
}
