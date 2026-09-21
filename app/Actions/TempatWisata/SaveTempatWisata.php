<?php

namespace App\Actions\TempatWisata;

use App\Models\TempatWisata;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SaveTempatWisata
{
    public function __construct(
        private GenerateTempatWisataSlug $generateSlug,
        private DeleteTempatWisataPhoto $deletePhoto,
    ) {}

    /**
     * @param  array{destinasi_id: int|string, nama: string, status: bool|int|string, unggulan: bool|int|string, urutan: int|string, foto_utama?: ?UploadedFile, galeri?: array<UploadedFile>, hapus_galeri?: array<int|string>, pengalaman?: array<int|string>, aktivitas?: array<int|string>, fasilitas?: array<int|string>, deskripsi_singkat?: ?string, deskripsi_lengkap?: ?string, lokasi?: ?string, jam_operasional?: ?string, harga_tiket?: int|float|string|null, waktu_terbaik?: ?string, tips?: ?string, slug?: null}  $data
     * @return array{tempat_wisata: TempatWisata, photos_cleaned: bool}
     */
    public function handle(array $data, ?TempatWisata $tempatWisata = null): array
    {
        $newPaths = [];
        $mainPath = null;
        $galleryPaths = [];
        $cleaned = true;

        try {
            if (($data['foto_utama'] ?? null) instanceof UploadedFile) {
                $mainPath = $this->upload($data['foto_utama'], 'foto_utama', $newPaths);
            }
            foreach ($data['galeri'] ?? [] as $index => $photo) {
                $galleryPaths[] = $this->upload($photo, 'galeri.'.$index, $newPaths);
            }

            for ($attempt = 0; $attempt < 5; $attempt++) {
                try {
                    $record = DB::transaction(function () use ($data, $tempatWisata, $mainPath, $galleryPaths, &$cleaned): TempatWisata {
                        $record = $tempatWisata === null ? new TempatWisata : TempatWisata::query()->lockForUpdate()->findOrFail($tempatWisata->id);
                        $oldPaths = [];
                        $removeIds = $data['hapus_galeri'] ?? [];
                        $gallery = $record->exists ? $record->galeri()->get() : collect();

                        if (count($removeIds) !== $gallery->whereIn('id', $removeIds)->count()) {
                            throw ValidationException::withMessages(['hapus_galeri' => 'Pilihan foto galeri sudah berubah. Muat ulang halaman.']);
                        }
                        if ($gallery->count() - count($removeIds) + count($galleryPaths) > 20) {
                            throw ValidationException::withMessages(['galeri' => 'Galeri maksimal 20 foto. Hapus beberapa foto terlebih dahulu.']);
                        }

                        $record->fill(Arr::except($data, ['slug', 'foto_utama', 'galeri', 'hapus_galeri', 'pengalaman', 'aktivitas', 'fasilitas']));
                        if (! $record->exists || $record->isDirty('nama')) {
                            $record->slug = $this->generateSlug->handle($record->nama, $record->exists ? $record->id : null);
                        }
                        if ($mainPath !== null) {
                            if ($record->foto_utama !== null) {
                                $oldPaths[] = $record->foto_utama;
                            }
                            $record->foto_utama = $mainPath;
                        }
                        if (! $record->saveOrFail()) {
                            throw new RuntimeException('Tempat wisata gagal disimpan.');
                        }
                        foreach (['pengalaman', 'aktivitas', 'fasilitas'] as $relation) {
                            $record->{$relation}()->sync($data[$relation] ?? []);
                        }

                        foreach ($gallery->whereIn('id', $removeIds) as $photo) {
                            $oldPaths[] = $photo->foto;
                            if (! $photo->delete()) {
                                throw new RuntimeException('Foto galeri gagal dihapus.');
                            }
                        }
                        $order = (int) ($gallery->max('urutan') ?? -1) + 1;
                        foreach ($galleryPaths as $path) {
                            $record->galeri()->create(['foto' => $path, 'urutan' => $order++]);
                        }

                        DB::afterCommit(function () use ($oldPaths, &$cleaned): void {
                            foreach (array_unique($oldPaths) as $path) {
                                $deleted = $this->deletePhoto->handle($path);
                                $cleaned = $deleted && $cleaned;
                            }
                        });

                        return $record;
                    });

                    return ['tempat_wisata' => $record, 'photos_cleaned' => $cleaned];
                } catch (UniqueConstraintViolationException $exception) {
                    $constraint = strtolower($exception->errorInfo[2] ?? '');
                    if (! str_contains($constraint, 'tempat_wisata_slug_unique') && ! str_contains($constraint, 'tempat_wisata.slug')) {
                        throw $exception;
                    }
                    if ($attempt === 4) {
                        throw ValidationException::withMessages(['nama' => 'Slug sedang digunakan oleh proses lain. Silakan simpan kembali.']);
                    }
                }
            }
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $this->deletePhoto->handle($path);
            }
            throw $exception;
        }

        throw new RuntimeException('Tempat wisata gagal disimpan.');
    }

    /** @param array<string> $newPaths */
    private function upload(UploadedFile $photo, string $field, array &$newPaths): string
    {
        $path = 'tempat-wisata/'.$photo->hashName();
        $newPaths[] = $path;
        try {
            if ($photo->store('tempat-wisata', 'public') === false) {
                throw new RuntimeException('Storage gagal menyimpan foto.');
            }
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([$field => 'Foto gagal disimpan. Silakan coba kembali.']);
        }

        return $path;
    }
}
