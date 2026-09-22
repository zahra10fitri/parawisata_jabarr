<?php

namespace App\Http\Controllers;

use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\TempatWisata;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DestinasiController extends Controller
{
    public function __invoke(Destinasi $destinasi): Response
    {
        abort_unless($destinasi->status, 404);
        $destinasi->load([
            'tempatWisata' => fn ($query) => $query->where('status', true)->with('pengalaman:id,nama')->orderByDesc('unggulan')->orderBy('urutan')->orderBy('id'),
            'paketPerjalanan' => fn ($query) => $query->where('status', true)->orderByDesc('unggulan')->orderBy('urutan')->orderBy('paket_perjalanan.id'),
        ]);

        return Inertia::render('destinasi/show', [
            'destinasi' => [
                ...$destinasi->only(['id', 'nama', 'slug', 'deskripsi_singkat', 'deskripsi_lengkap', 'lokasi', 'waktu_terbaik', 'cara_menuju', 'transportasi_lokal', 'tips', 'unggulan']),
                'foto_url' => $this->imageUrl($destinasi->foto_utama),
                'tempat_wisata' => $destinasi->tempatWisata->map(fn (TempatWisata $wisata): array => [
                    ...$wisata->only(['id', 'nama', 'slug', 'deskripsi_singkat', 'lokasi', 'harga_tiket', 'unggulan']),
                    'foto_url' => $this->imageUrl($wisata->foto_utama),
                    'pengalaman' => $wisata->pengalaman->map->only(['id', 'nama'])->all(),
                ])->all(),
                'paket_perjalanan' => $destinasi->paketPerjalanan->map(fn (PaketPerjalanan $paket): array => [
                    ...$paket->only(['id', 'nama', 'slug', 'durasi_hari', 'harga_mulai', 'unggulan']),
                    'foto_url' => $this->imageUrl($paket->foto_utama),
                ])->all(),
            ],
        ]);
    }

    private function imageUrl(?string $path): ?string
    {
        return $path === null ? null : Storage::disk('public')->url($path);
    }
}
