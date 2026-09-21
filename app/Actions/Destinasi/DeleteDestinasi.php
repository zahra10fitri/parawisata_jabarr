<?php

namespace App\Actions\Destinasi;

use App\Models\Destinasi;
use App\Models\TempatWisata;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class DeleteDestinasi
{
    public function __construct(private DeleteDestinasiPhoto $deletePhoto) {}

    public function handle(Destinasi $destinasi): bool
    {
        $photoCleaned = true;

        DB::transaction(function () use ($destinasi, &$photoCleaned): void {
            $record = Destinasi::query()->lockForUpdate()->findOrFail($destinasi->id);
            if (TempatWisata::query()->where('destinasi_id', $record->id)->exists()) {
                throw ValidationException::withMessages(['destinasi' => 'Destinasi masih memiliki tempat wisata. Pindahkan atau hapus tempat wisata terlebih dahulu.']);
            }
            $path = $record->foto_utama;

            if (! $record->delete()) {
                throw new RuntimeException('Destinasi gagal dihapus.');
            }

            DB::afterCommit(function () use ($path, &$photoCleaned): void {
                $photoCleaned = $this->deletePhoto->handle($path);
            });
        });

        return $photoCleaned;
    }
}
