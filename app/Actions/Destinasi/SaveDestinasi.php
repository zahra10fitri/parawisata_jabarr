<?php

namespace App\Actions\Destinasi;

use App\Models\Destinasi;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class SaveDestinasi
{
    public function __construct(
        private GenerateDestinasiSlug $generateSlug,
        private DeleteDestinasiPhoto $deletePhoto,
    ) {}

    /**
     * @param  array{nama: string, status: bool|int|string, unggulan: bool|int|string, urutan: int|string, deskripsi_singkat?: ?string, deskripsi_lengkap?: ?string, lokasi?: ?string, waktu_terbaik?: ?string, cara_menuju?: ?string, transportasi_lokal?: ?string, tips?: ?string, foto_utama?: ?UploadedFile, slug?: null}  $data
     * @return array{destinasi: Destinasi, photo_cleaned: bool}
     */
    public function handle(array $data, ?Destinasi $destinasi = null): array
    {
        $photo = $data['foto_utama'] ?? null;
        $attributes = Arr::except($data, ['foto_utama', 'slug']);
        $newPath = null;
        $photoCleaned = true;

        if ($photo instanceof UploadedFile) {
            try {
                $newPath = $photo->store('destinasi', 'public');
                if ($newPath === false) {
                    throw ValidationException::withMessages(['foto_utama' => 'Foto gagal disimpan. Silakan coba kembali.']);
                }
            } catch (Throwable $exception) {
                report($exception);
                throw ValidationException::withMessages(['foto_utama' => 'Foto gagal disimpan. Silakan coba kembali.']);
            }
        }

        try {
            for ($attempt = 0; $attempt < 5; $attempt++) {
                try {
                    $saved = DB::transaction(function () use ($destinasi, $attributes, $newPath, &$photoCleaned): Destinasi {
                        $record = $destinasi === null
                            ? new Destinasi
                            : Destinasi::query()->lockForUpdate()->findOrFail($destinasi->id);
                        $oldPath = $record->foto_utama;
                        $record->fill($attributes);

                        if (! $record->exists || $record->isDirty('nama')) {
                            $record->slug = $this->generateSlug->handle($record->nama, $record->exists ? $record->id : null);
                        }

                        if ($newPath !== null) {
                            $record->foto_utama = $newPath;
                        }

                        if (! $record->saveOrFail()) {
                            throw new RuntimeException('Destinasi gagal disimpan.');
                        }

                        if ($newPath !== null && $oldPath !== null) {
                            DB::afterCommit(function () use ($oldPath, &$photoCleaned): void {
                                $photoCleaned = $this->deletePhoto->handle($oldPath);
                            });
                        }

                        return $record;
                    });

                    return ['destinasi' => $saved, 'photo_cleaned' => $photoCleaned];
                } catch (UniqueConstraintViolationException $exception) {
                    $constraint = strtolower($exception->errorInfo[2] ?? '');
                    if (! str_contains($constraint, 'destinasi_slug_unique') && ! str_contains($constraint, 'destinasi.slug')) {
                        throw $exception;
                    }

                    if ($attempt === 4) {
                        throw ValidationException::withMessages(['nama' => 'Slug sedang digunakan oleh proses lain. Silakan simpan kembali.']);
                    }
                }
            }
        } catch (Throwable $exception) {
            if (is_string($newPath)) {
                $this->deletePhoto->handle($newPath);
            }

            throw $exception;
        }

        throw ValidationException::withMessages(['nama' => 'Destinasi gagal disimpan.']);
    }
}
