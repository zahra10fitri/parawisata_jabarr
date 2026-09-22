<?php

namespace Database\Factories;

use App\Models\PesanKontak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PesanKontak>
 */
class PesanKontakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['nama' => fake()->name(), 'email' => fake()->safeEmail(), 'whatsapp' => fake()->optional()->numerify('08##########'), 'subjek' => fake()->sentence(5), 'pesan' => fake()->paragraphs(2, true), 'dibaca_pada' => null];
    }
}
