<?php

namespace App\Actions\Destinasi;

use App\Models\Destinasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DeleteDestinasiPhoto
{
    public function handle(?string $path): bool
    {
        if ($path === null) {
            return true;
        }

        try {
            if (! preg_match('/\Adestinasi\/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)\z/', $path)) {
                throw new RuntimeException('Cleanup foto destinasi ditolak: path di luar format kelolaan.');
            }

            if (Destinasi::query()->where('foto_utama', $path)->exists()) {
                return true;
            }

            $disk = Storage::disk('public');

            if ($disk->exists($path) && ! $disk->delete($path)) {
                throw new RuntimeException('File foto destinasi gagal dihapus.');
            }

            return true;
        } catch (Throwable $exception) {
            Log::warning('Cleanup foto destinasi belum selesai.', ['path' => $path, 'exception' => $exception]);

            return false;
        }
    }
}
