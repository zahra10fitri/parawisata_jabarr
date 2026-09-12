<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Destinasi\DeleteDestinasi;
use App\Actions\Destinasi\SaveDestinasi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexDestinasiRequest;
use App\Http\Requests\Admin\StoreDestinasiRequest;
use App\Http\Requests\Admin\UpdateDestinasiRequest;
use App\Models\Destinasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DestinasiController extends Controller
{
    public function index(IndexDestinasiRequest $request): Response
    {
        $search = $request->validated('search') ?? '';
        $query = Destinasi::query()->select(['id', 'nama', 'slug', 'foto_utama', 'lokasi', 'status', 'unggulan', 'urutan']);

        if ($search !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
            $query->whereRaw("nama LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
        }

        $destinasi = $query->orderBy('urutan')->orderBy('id')
            ->paginate(15)->appends(['search' => $search])
            ->through(fn (Destinasi $item): array => $this->serialize($item));

        return Inertia::render('admin/destinasi/index', [
            'destinasi' => $destinasi,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/destinasi/create');
    }

    public function store(StoreDestinasiRequest $request, SaveDestinasi $save): RedirectResponse
    {
        $result = $save->handle($request->validated());
        $this->flashResult('Destinasi berhasil ditambahkan.', $result['photo_cleaned']);

        return to_route('admin.destinasi.show', $result['destinasi']);
    }

    public function show(Destinasi $destinasi): Response
    {
        return Inertia::render('admin/destinasi/show', ['destinasi' => $this->serialize($destinasi)]);
    }

    public function edit(Destinasi $destinasi): Response
    {
        return Inertia::render('admin/destinasi/edit', ['destinasi' => $this->serialize($destinasi)]);
    }

    public function update(UpdateDestinasiRequest $request, Destinasi $destinasi, SaveDestinasi $save): RedirectResponse
    {
        $result = $save->handle($request->validated(), $destinasi);
        $this->flashResult('Destinasi berhasil diperbarui.', $result['photo_cleaned']);

        return to_route('admin.destinasi.show', $result['destinasi']);
    }

    public function destroy(Destinasi $destinasi, DeleteDestinasi $delete): RedirectResponse
    {
        $this->flashResult('Destinasi berhasil dihapus.', $delete->handle($destinasi));

        return to_route('admin.destinasi.index');
    }

    /** @return array<string, mixed> */
    private function serialize(Destinasi $destinasi): array
    {
        return [
            ...$destinasi->toArray(),
            'foto_utama_url' => $destinasi->foto_utama === null ? null : Storage::disk('public')->url($destinasi->foto_utama),
        ];
    }

    private function flashResult(string $message, bool $photoCleaned): void
    {
        Inertia::flash('toast', [
            'type' => $photoCleaned ? 'success' : 'warning',
            'message' => $message.($photoCleaned ? '' : ' Pembersihan foto lama belum selesai; detail tercatat pada log aplikasi.'),
        ]);
    }
}
