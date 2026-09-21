<?php

namespace Database\Factories;

use App\Models\GaleriTempatWisata;
use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<GaleriTempatWisata> */
class GaleriTempatWisataFactory extends Factory
{
    /** @return array{tempat_wisata_id: Factory, foto: string, urutan: int} */
    public function definition(): array
    {
        return [
            'tempat_wisata_id' => TempatWisata::factory(),
            'foto' => 'tempat-wisata/'.fake()->uuid().'.jpg',
            'urutan' => 0,
        ];
    }
}
