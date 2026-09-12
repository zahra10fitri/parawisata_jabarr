<?php

namespace Database\Seeders;

use App\Models\Aktivitas;
use Illuminate\Database\Seeder;

class AktivitasSeeder extends Seeder
{
    public function run(): void
    {
        $aktivitas = [
            'hiking' => 'Hiking',
            'camping' => 'Camping',
            'fotografi' => 'Fotografi',
            'snorkeling' => 'Snorkeling',
        ];

        foreach ($aktivitas as $slug => $nama) {
            Aktivitas::firstOrCreate(
                ['slug' => $slug],
                ['nama' => $nama, 'status' => true],
            );
        }
    }
}
