<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->call([
                PengalamanSeeder::class,
                AktivitasSeeder::class,
                FasilitasSeeder::class,
            ]);
        });
    }
}
