<?php

namespace Database\Seeders;

use App\Models\Pengalaman;
use Illuminate\Database\Seeder;

class PengalamanSeeder extends Seeder
{
    public function run(): void
    {
        $pengalaman = [
            'alam' => 'Alam',
            'petualangan' => 'Petualangan',
            'budaya' => 'Budaya',
            'kuliner' => 'Kuliner',
            'pantai' => 'Pantai',
            'keluarga' => 'Keluarga',
            'hidden-gem' => 'Hidden Gem',
        ];

        foreach ($pengalaman as $slug => $nama) {
            Pengalaman::firstOrCreate(
                ['slug' => $slug],
                ['nama' => $nama, 'status' => true],
            );
        }
    }
}
