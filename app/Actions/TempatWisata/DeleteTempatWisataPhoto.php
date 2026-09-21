<?php

namespace App\Actions\TempatWisata;

use App\Models\GaleriTempatWisata;
use App\Models\TempatWisata;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DeleteTempatWisataPhoto
{
    public function handle(?string $path): bool
    {
        if ($path === null) {
            return true;
        }
        try {
            if (! preg_match('/\Atempat-wisata\/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)\z/', $path)) {
                throw new RuntimeException('Path foto di luar direktori kelolaan.');
            }
            if (TempatWisata::query()->where('foto_utama', $path)->exists() || GaleriTempatWisata::query()->where('foto', $path)->exists()) {
                return true;
            }
            $disk = Storage::disk('public');
            if ($disk->exists($path) && ! $disk->delete($path)) {
                throw new RuntimeException('File foto gagal dihapus.');
            }

            return true;
        } catch (Throwable $exception) {
            Log::warning('Cleanup foto tempat wisata belum selesai.', ['path' => $path, 'exception' => $exception]);

            return false;
        }
    }
}
