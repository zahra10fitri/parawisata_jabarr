<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaketPerjalananRequest;
use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\Pengalaman;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PaketPerjalananController extends Controller
{
    public function index(PaketPerjalananRequest $request): Response
    {
        $filters = ['destinasi_id' => (string) ($request->validated('destinasi_id') ?? ''), 'durasi' => (string) ($request->validated('durasi') ?? ''), 'pengalaman_id' => (string) ($request->validated('pengalaman_id') ?? '')];
        $query = PaketPerjalanan::where('status', true)->with(['destinasi:id,nama', 'tempatWisata.pengalaman']);
        if ($filters['destinasi_id'] !== '') {
            $query->whereHas('destinasi', fn ($q) => $q->whereKey($filters['destinasi_id']));
        } if ($filters['durasi'] !== '') {
            $query->where('durasi_hari', $filters['durasi']);
        } if ($filters['pengalaman_id'] !== '') {
            $query->whereHas('tempatWisata.pengalaman', fn ($q) => $q->whereKey($filters['pengalaman_id']));
        }

        return Inertia::render('paket-perjalanan/index', ['paketPerjalanan' => $query->orderByDesc('unggulan')->orderBy('urutan')->paginate(12)->withQueryString()->through(fn (PaketPerjalanan $paket) => $this->summary($paket)), 'filters' => $filters, 'destinasiOptions' => Destinasi::orderBy('nama')->get(['id', 'nama']), 'pengalamanOptions' => Pengalaman::orderBy('nama')->get(['id', 'nama'])]);
    }

    public function show(PaketPerjalanan $paketPerjalanan): Response
    {
        abort_unless($paketPerjalanan->status, 404);
        $paketPerjalanan->load(['destinasi:id,nama', 'tempatWisata:id,nama,slug,destinasi_id', 'itinerary.kegiatan']);

        return Inertia::render('paket-perjalanan/show', ['paketPerjalanan' => [...$this->summary($paketPerjalanan), ...$paketPerjalanan->only(['deskripsi', 'highlight', 'termasuk', 'tidak_termasuk']), 'tempat_wisata' => $paketPerjalanan->tempatWisata->map->only(['id', 'nama', 'slug'])->all(), 'itinerary' => $paketPerjalanan->itinerary->map(fn ($hari): array => [...$hari->only(['id', 'hari_ke', 'judul']), 'kegiatan' => $hari->kegiatan->map->only(['id', 'judul', 'keterangan', 'waktu'])->all()])->all()]]);
    }

    private function summary(PaketPerjalanan $paket): array
    {
        return [...$paket->only(['id', 'nama', 'slug', 'durasi_hari', 'harga_mulai', 'unggulan']), 'foto_url' => $paket->foto_utama ? Storage::disk('public')->url($paket->foto_utama) : null, 'destinasi' => $paket->destinasi->map->only(['id', 'nama'])->all()];
    }
}
