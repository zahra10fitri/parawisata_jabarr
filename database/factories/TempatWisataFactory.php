<?php

namespace Database\Factories;

use App\Models\Destinasi;
use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TempatWisata> */
class TempatWisataFactory extends Factory
{
    /** @return array{destinasi_id: Factory, nama: string, slug: string} */
    public function definition(): array
    {
        $nama = fake()->unique()->sentence(3);

        return ['destinasi_id' => Destinasi::factory(), 'nama' => $nama, 'slug' => Str::slug($nama)];
    }

    public function aktif(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => true]);
    }

    public function unggulan(): static
    {
        return $this->state(fn (array $attributes): array => ['unggulan' => true]);
    }
}
