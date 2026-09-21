<?php

namespace App\Http\Controllers\Admin;

use App\Actions\TempatWisata\DeleteTempatWisata;
use App\Actions\TempatWisata\SaveTempatWisata;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexTempatWisataRequest;
use App\Http\Requests\Admin\StoreTempatWisataRequest;
use App\Http\Requests\Admin\UpdateTempatWisataRequest;
use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\GaleriTempatWisata;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TempatWisataController extends Controller
{
    public function index(IndexTempatWisataRequest $request): Response
    {
        $search = $request->validated('search') ?? '';
        $destinasiId = $request->validated('destinasi_id');
        $query = TempatWisata::query()->with('destinasi:id,nama')
            ->select(['id', 'destinasi_id', 'nama', 'slug', 'foto_utama', 'lokasi', 'status', 'unggulan', 'urutan']);
        if ($search !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
            $query->whereRaw("nama LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
        }
        if ($destinasiId !== null) {
            $query->where('destinasi_id', $destinasiId);
        }

        return Inertia::render('admin/tempat-wisata/index', [
            'tempatWisata' => $query->orderBy('urutan')->orderBy('id')->paginate(15)
                ->appends(['search' => $search, 'destinasi_id' => $destinasiId])
                ->through(fn (TempatWisata $item): array => $this->serialize($item)),
            'filters' => ['search' => $search, 'destinasi_id' => $destinasiId === null ? '' : (string) $destinasiId],
            'destinasiOptions' => Destinasi::query()->orderBy('nama')->get(['id', 'nama', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/tempat-wisata/create', ['options' => $this->options()]);
    }

    public function store(StoreTempatWisataRequest $request, SaveTempatWisata $save): RedirectResponse
    {
        $result = $save->handle($request->validated());
        $this->flashResult('Tempat wisata berhasil ditambahkan.', $result['photos_cleaned']);

        return to_route('admin.tempat-wisata.show', $result['tempat_wisata']);
    }

    public function show(TempatWisata $tempatWisata): Response
    {
        $tempatWisata->load(['destinasi:id,nama', 'pengalaman:id,nama,status', 'aktivitas:id,nama,status', 'fasilitas:id,nama,status', 'galeri']);

        return Inertia::render('admin/tempat-wisata/show', ['tempatWisata' => $this->serialize($tempatWisata)]);
    }

    public function edit(TempatWisata $tempatWisata): Response
    {
        $tempatWisata->load(['destinasi:id,nama', 'pengalaman:id,nama,status', 'aktivitas:id,nama,status', 'fasilitas:id,nama,status', 'galeri']);

        return Inertia::render('admin/tempat-wisata/edit', [
            'tempatWisata' => $this->serialize($tempatWisata),
            'options' => $this->options(),
        ]);
    }

    public function update(UpdateTempatWisataRequest $request, TempatWisata $tempatWisata, SaveTempatWisata $save): RedirectResponse
    {
        $result = $save->handle($request->validated(), $tempatWisata);
        $this->flashResult('Tempat wisata berhasil diperbarui.', $result['photos_cleaned']);

        return to_route('admin.tempat-wisata.show', $result['tempat_wisata']);
    }

    public function destroy(TempatWisata $tempatWisata, DeleteTempatWisata $delete): RedirectResponse
    {
        $this->flashResult('Tempat wisata berhasil dihapus.', $delete->handle($tempatWisata));

        return to_route('admin.tempat-wisata.index');
    }

    /** @return array<string, mixed> */
    private function options(): array
    {
        return [
            'destinasi' => Destinasi::query()->orderBy('nama')->get(['id', 'nama', 'status']),
            'pengalaman' => Pengalaman::query()->orderBy('nama')->get(['id', 'nama', 'status']),
            'aktivitas' => Aktivitas::query()->orderBy('nama')->get(['id', 'nama', 'status']),
            'fasilitas' => Fasilitas::query()->orderBy('nama')->get(['id', 'nama', 'status']),
        ];
    }

    /** @return array<string, mixed> */
    private function serialize(TempatWisata $tempatWisata): array
    {
        $data = [
            ...$tempatWisata->toArray(),
            'foto_utama_url' => $tempatWisata->foto_utama === null ? null : Storage::disk('public')->url($tempatWisata->foto_utama),
        ];
        if ($tempatWisata->relationLoaded('galeri')) {
            $data['galeri'] = $tempatWisata->galeri->map(fn (GaleriTempatWisata $photo): array => [
                'id' => $photo->id, 'foto' => $photo->foto, 'urutan' => $photo->urutan,
                'url' => Storage::disk('public')->url($photo->foto),
            ])->all();
        }

        return $data;
    }

    private function flashResult(string $message, bool $cleaned): void
    {
        Inertia::flash('toast', [
            'type' => $cleaned ? 'success' : 'warning',
            'message' => $message.($cleaned ? '' : ' Pembersihan foto belum selesai; detail tercatat pada log aplikasi.'),
        ]);
    }
}
