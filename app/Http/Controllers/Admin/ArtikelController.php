<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexArtikelRequest;
use App\Http\Requests\Admin\StoreArtikelRequest;
use App\Http\Requests\Admin\UpdateArtikelRequest;
use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\KategoriArtikel;
use App\Models\TempatWisata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArtikelController extends Controller
{
    public function index(IndexArtikelRequest $request): Response
    {
        $filters = ['search' => $request->validated('search') ?? '', 'kategori_id' => (string) ($request->validated('kategori_id') ?? ''), 'status' => (string) ($request->validated('status') ?? '')];
        $query = Artikel::query()->with('kategori:id,nama');
        if ($filters['search'] !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']);
            $query->whereRaw("judul LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
        } if ($filters['kategori_id'] !== '') {
            $query->where('kategori_artikel_id', $filters['kategori_id']);
        } if ($filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

return Inertia::render('admin/artikel/index', ['artikel' => $query->latest()->paginate(15)->withQueryString()->through(fn (Artikel $artikel) => $this->summary($artikel)), 'filters' => $filters, 'kategoriOptions' => KategoriArtikel::orderBy('nama')->get(['id', 'nama'])]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/artikel/create', ['options' => $this->options()]);
    }

    public function store(StoreArtikelRequest $request): RedirectResponse
    {
        return to_route('admin.artikel.show', $this->save($request->validated()));
    }

    public function show(Artikel $artikel): Response
    {
        $artikel->load(['kategori:id,nama', 'destinasi:id,nama', 'tempatWisata:id,nama']);

        return Inertia::render('admin/artikel/show', ['artikel' => $this->detail($artikel)]);
    }

    public function edit(Artikel $artikel): Response
    {
        $artikel->load(['kategori:id,nama', 'destinasi:id,nama', 'tempatWisata:id,nama,destinasi_id']);

        return Inertia::render('admin/artikel/edit', ['artikel' => $this->detail($artikel), 'options' => $this->options()]);
    }

    public function update(UpdateArtikelRequest $request, Artikel $artikel): RedirectResponse
    {
        return to_route('admin.artikel.show', $this->save($request->validated(), $artikel));
    }

    public function destroy(Artikel $artikel): RedirectResponse
    {
        if ($artikel->foto_utama) {
            Storage::disk('public')->delete($artikel->foto_utama);
        } $artikel->delete();

        return to_route('admin.artikel.index');
    }

    private function save(array $data, ?Artikel $artikel = null): Artikel
    {
        $artikel ??= new Artikel;
        $photo = $data['foto_utama'] ?? null;
        if ($photo) {
            $old = $artikel->foto_utama;
            $data['foto_utama'] = $photo->store('artikel', 'public');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        } else {
            unset($data['foto_utama']);
        } $artikel->fill(Arr::except($data, ['destinasi', 'tempat_wisata', 'slug']));
        if (! $artikel->exists || $artikel->isDirty('judul')) {
            $base = Str::slug($artikel->judul) ?: 'artikel';
            $artikel->slug = $base;
            $i = 2;
            while (Artikel::query()->where('slug', $artikel->slug)->when($artikel->exists, fn ($query) => $query->whereKeyNot($artikel->id))->exists()) {
                $artikel->slug = Str::limit($base, 255 - strlen('-'.$i), '').'-'.$i++;
            }
        } $artikel->save();
        $artikel->destinasi()->sync($data['destinasi'] ?? []);
        $artikel->tempatWisata()->sync($data['tempat_wisata'] ?? []);

        return $artikel;
    }

    private function options(): array
    {
        return ['kategori' => KategoriArtikel::orderBy('nama')->get(['id', 'nama']), 'destinasi' => Destinasi::orderBy('nama')->get(['id', 'nama']), 'tempatWisata' => TempatWisata::orderBy('nama')->get(['id', 'nama', 'destinasi_id'])];
    }

    private function summary(Artikel $artikel): array
    {
        return [...$artikel->only(['id', 'kategori_artikel_id', 'judul', 'slug', 'ringkasan', 'status', 'unggulan', 'tanggal_publikasi']), 'foto_url' => $artikel->foto_utama ? Storage::disk('public')->url($artikel->foto_utama) : null, 'kategori' => $artikel->kategori->only(['id', 'nama'])];
    }

    private function detail(Artikel $artikel): array
    {
        return [...$this->summary($artikel), ...$artikel->only(['konten']), 'destinasi' => $artikel->destinasi->map->only(['id', 'nama'])->all(), 'tempat_wisata' => $artikel->tempatWisata->map->only(['id', 'nama', 'destinasi_id'])->all()];
    }
}
