<?php

namespace App\Http\Controllers;

use App\Http\Requests\JelajahiRequest;
use App\Models\Destinasi;
use App\Models\GaleriTempatWisata;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class JelajahiController extends Controller
{
    public function index(JelajahiRequest $request): Response
    {
        $filters = [
            'search' => $request->validated('search') ?? '',
            'destinasi_id' => (string) ($request->validated('destinasi_id') ?? ''),
            'pengalaman_id' => (string) ($request->validated('pengalaman_id') ?? ''),
        ];
        $query = TempatWisata::query()->where('status', true)->with(['destinasi:id,nama', 'pengalaman:id,nama'])
            ->select(['id', 'destinasi_id', 'nama', 'slug', 'deskripsi_singkat', 'foto_utama', 'lokasi', 'harga_tiket', 'unggulan', 'urutan']);

        if ($filters['search'] !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']);
            $query->whereRaw("nama LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
        }
        if ($filters['destinasi_id'] !== '') {
            $query->where('destinasi_id', $filters['destinasi_id']);
        }
        if ($filters['pengalaman_id'] !== '') {
            $query->whereHas('pengalaman', fn (Builder $relation): Builder => $relation->where('pengalaman.id', $filters['pengalaman_id']));
        }

        return Inertia::render('jelajahi/index', [
            'wisata' => $query->orderByDesc('unggulan')->orderBy('urutan')->orderBy('id')
                ->paginate(12)->appends($filters)->through(fn (TempatWisata $item): array => $this->summary($item)),
            'filters' => $filters,
            'destinasiOptions' => Destinasi::query()->orderBy('nama')->get(['id', 'nama']),
            'pengalamanOptions' => Pengalaman::query()->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function show(TempatWisata $tempatWisata): Response
    {
        abort_unless($tempatWisata->status, 404);
        $tempatWisata->load(['destinasi:id,nama', 'pengalaman:id,nama', 'aktivitas:id,nama', 'fasilitas:id,nama', 'galeri']);

        return Inertia::render('jelajahi/show', [
            'wisata' => [
                ...$this->summary($tempatWisata),
                ...$tempatWisata->only(['deskripsi_lengkap', 'jam_operasional', 'waktu_terbaik', 'tips']),
                'aktivitas' => $tempatWisata->aktivitas->map->only(['id', 'nama'])->all(),
                'fasilitas' => $tempatWisata->fasilitas->map->only(['id', 'nama'])->all(),
                'galeri' => $tempatWisata->galeri->map(fn (GaleriTempatWisata $photo): array => [
                    'id' => $photo->id, 'url' => Storage::disk('public')->url($photo->foto),
                ])->all(),
            ],
        ]);
    }

    /** @return array<string, mixed> */
    private function summary(TempatWisata $wisata): array
    {
        return [
            ...$wisata->only(['id', 'nama', 'slug', 'deskripsi_singkat', 'lokasi', 'harga_tiket', 'unggulan']),
            'foto_url' => $wisata->foto_utama === null ? null : Storage::disk('public')->url($wisata->foto_utama),
            'destinasi' => $wisata->destinasi->only(['id', 'nama']),
            'pengalaman' => $wisata->pengalaman->map->only(['id', 'nama'])->all(),
        ];
    }
}
