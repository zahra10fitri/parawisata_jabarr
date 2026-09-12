<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use Illuminate\Database\Seeder;

class FasilitasSeeder extends Seeder
{
    public function run(): void
    {
        $fasilitas = [
            'area-parkir' => 'Area Parkir',
            'toilet' => 'Toilet',
            'musala' => 'Musala',
            'tempat-makan' => 'Tempat Makan',
        ];

        foreach ($fasilitas as $slug => $nama) {
            Fasilitas::firstOrCreate(
                ['slug' => $slug],
                ['nama' => $nama, 'status' => true],
            );
        }
    }
}
