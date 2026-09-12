<?php

namespace Database\Factories;

use App\Models\Destinasi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Destinasi>
 */
class DestinasiFactory extends Factory
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

    /**
     * Indicate that the destination is featured.
     */
    public function unggulan(): static
    {
        return $this->state(fn (array $attributes): array => [
            'unggulan' => true,
        ]);
    }
}
