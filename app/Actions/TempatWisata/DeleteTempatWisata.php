<?php

namespace App\Actions\TempatWisata;

use App\Models\TempatWisata;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeleteTempatWisata
{
    public function __construct(private DeleteTempatWisataPhoto $deletePhoto) {}

    public function handle(TempatWisata $tempatWisata): bool
    {
        $cleaned = true;
        DB::transaction(function () use ($tempatWisata, &$cleaned): void {
            $record = TempatWisata::query()->with('galeri')->lockForUpdate()->findOrFail($tempatWisata->id);
            $paths = $record->galeri->pluck('foto')->push($record->foto_utama)->filter()->unique()->all();
            if (! $record->delete()) {
                throw new RuntimeException('Tempat wisata gagal dihapus.');
            }
            DB::afterCommit(function () use ($paths, &$cleaned): void {
                foreach ($paths as $path) {
                    $deleted = $this->deletePhoto->handle($path);
                    $cleaned = $deleted && $cleaned;
                }
            });
        });

        return $cleaned;
    }
}
