<?php

namespace App\Http\Controllers;

use App\Http\Requests\InspirasiRequest;
use App\Models\Artikel;
use App\Models\KategoriArtikel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InspirasiController extends Controller
{
    public function index(InspirasiRequest $request): Response
    {
        $filters = ['search' => $request->validated('search') ?? '', 'kategori_id' => (string) ($request->validated('kategori_id') ?? '')];
        $query = $this->published()->with('kategori:id,nama');
        if ($filters['search'] !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']);
            $query->where(function (Builder $query) use ($escaped): void {
                $query->whereRaw("judul LIKE ? ESCAPE '!'", ['%'.$escaped.'%'])->orWhereRaw("ringkasan LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
            });
        } if ($filters['kategori_id'] !== '') {
            $query->where('kategori_artikel_id', $filters['kategori_id']);
        }

return Inertia::render('inspirasi/index', ['artikel' => $query->orderByDesc('tanggal_publikasi')->paginate(12)->withQueryString()->through(fn (Artikel $artikel) => $this->summary($artikel)), 'unggulan' => $this->published()->where('unggulan', true)->with('kategori:id,nama')->latest('tanggal_publikasi')->limit(3)->get()->map(fn (Artikel $artikel) => $this->summary($artikel)), 'filters' => $filters, 'kategoriOptions' => KategoriArtikel::orderBy('nama')->get(['id', 'nama'])]);
    }

    public function show(Artikel $artikel): Response
    {
        abort_unless($artikel->status && $artikel->tanggal_publikasi?->isPast(), 404);
        $artikel->load(['kategori:id,nama', 'destinasi:id,nama,slug', 'tempatWisata:id,nama,slug']);

        return Inertia::render('inspirasi/show', ['artikel' => [...$this->summary($artikel), ...$artikel->only(['konten']), 'destinasi' => $artikel->destinasi->map->only(['id', 'nama', 'slug'])->all(), 'tempat_wisata' => $artikel->tempatWisata->map->only(['id', 'nama', 'slug'])->all()]]);
    }

    private function published(): Builder
    {
        return Artikel::query()->where('status', true)->whereNotNull('tanggal_publikasi')->where('tanggal_publikasi', '<=', now());
    }

    private function summary(Artikel $artikel): array
    {
        return [...$artikel->only(['id', 'judul', 'slug', 'ringkasan', 'unggulan', 'tanggal_publikasi']), 'foto_url' => $artikel->foto_utama ? Storage::disk('public')->url($artikel->foto_utama) : null, 'kategori' => $artikel->kategori->only(['id', 'nama'])];
    }
}
