<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPaketPerjalananRequest;
use App\Http\Requests\Admin\StorePaketPerjalananRequest;
use App\Http\Requests\Admin\UpdatePaketPerjalananRequest;
use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\TempatWisata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PaketPerjalananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPaketPerjalananRequest $request): Response
    {
        $filters = ['search' => $request->validated('search') ?? '', 'destinasi_id' => (string) ($request->validated('destinasi_id') ?? ''), 'status' => (string) ($request->validated('status') ?? '')];
        $query = PaketPerjalanan::query()->with('destinasi:id,nama');
        if ($filters['search'] !== '') {
            $query->where('nama', 'like', '%'.str_replace(['%', '_'], ['\\%', '\\_'], $filters['search']).'%');
        }
        if ($filters['destinasi_id'] !== '') {
            $query->whereHas('destinasi', fn ($query) => $query->whereKey($filters['destinasi_id']));
        }
        if ($filters['status'] !== '') {
            $query->where('status', (bool) $filters['status']);
        }

        return Inertia::render('admin/paket-perjalanan/index', ['paketPerjalanan' => $query->orderBy('urutan')->paginate(15)->withQueryString()->through(fn (PaketPerjalanan $paket) => $this->summary($paket)), 'filters' => $filters, 'destinasiOptions' => Destinasi::orderBy('nama')->get(['id', 'nama'])]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('admin/paket-perjalanan/create', ['options' => $this->options()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaketPerjalananRequest $request): RedirectResponse
    {
        $paket = $this->save($request->validated());

        return to_route('admin.paket-perjalanan.show', $paket);
    }

    /**
     * Display the specified resource.
     */
    public function show(PaketPerjalanan $paketPerjalanan): Response
    {
        $paketPerjalanan->load(['destinasi:id,nama', 'tempatWisata:id,nama,destinasi_id']);

        return Inertia::render('admin/paket-perjalanan/show', ['paketPerjalanan' => $this->detail($paketPerjalanan)]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaketPerjalanan $paketPerjalanan): Response
    {
        $paketPerjalanan->load(['destinasi:id,nama', 'tempatWisata:id,nama,destinasi_id']);

        return Inertia::render('admin/paket-perjalanan/edit', ['paketPerjalanan' => $this->detail($paketPerjalanan), 'options' => $this->options()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaketPerjalananRequest $request, PaketPerjalanan $paketPerjalanan): RedirectResponse
    {
        $paket = $this->save($request->validated(), $paketPerjalanan);

        return to_route('admin.paket-perjalanan.show', $paket);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaketPerjalanan $paketPerjalanan): RedirectResponse
    {
        if ($paketPerjalanan->foto_utama !== null) {
            Storage::disk('public')->delete($paketPerjalanan->foto_utama);
        }
        $paketPerjalanan->delete();

        return to_route('admin.paket-perjalanan.index');
    }

    private function save(array $data, ?PaketPerjalanan $paket = null): PaketPerjalanan
    {
        $paket ??= new PaketPerjalanan;
        $path = $data['foto_utama'] ?? null;
        if ($path !== null) {
            if ($paket->foto_utama !== null) {
                Storage::disk('public')->delete($paket->foto_utama);
            } $data['foto_utama'] = $path->store('paket-perjalanan', 'public');
        } else {
            unset($data['foto_utama']);
        } $paket->fill($data);
        if (! $paket->exists || $paket->isDirty('nama')) {
            $paket->slug = Str::slug($paket->nama) ?: 'paket-perjalanan';
            $base = $paket->slug;
            $i = 2;
            while (PaketPerjalanan::where('slug', $paket->slug)->when($paket->exists, fn ($q) => $q->whereKeyNot($paket->id))->exists()) {
                $paket->slug = Str::limit($base, 255 - strlen('-'.$i), '').'-'.$i++;
            }
        } $paket->save();
        $paket->destinasi()->sync($data['destinasi'] ?? []);
        $paket->tempatWisata()->sync($data['tempat_wisata'] ?? []);

        return $paket;
    }

    private function options(): array
    {
        return ['destinasi' => Destinasi::orderBy('nama')->get(['id', 'nama', 'status']), 'tempatWisata' => TempatWisata::orderBy('nama')->get(['id', 'nama', 'destinasi_id', 'status'])];
    }

    private function summary(PaketPerjalanan $paket): array
    {
        return [...$paket->only(['id', 'nama', 'slug', 'durasi_hari', 'harga_mulai', 'status', 'unggulan', 'urutan']), 'foto_url' => $paket->foto_utama ? Storage::disk('public')->url($paket->foto_utama) : null, 'destinasi' => $paket->destinasi->map->only(['id', 'nama'])->all()];
    }

    private function detail(PaketPerjalanan $paket): array
    {
        return [...$this->summary($paket), ...$paket->only(['deskripsi', 'highlight', 'termasuk', 'tidak_termasuk']), 'tempat_wisata' => $paket->tempatWisata->map->only(['id', 'nama', 'destinasi_id'])->all()];
    }
}
