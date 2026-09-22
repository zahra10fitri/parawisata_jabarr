<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexAktivitasRequest;
use App\Http\Requests\Admin\StoreAktivitasRequest;
use App\Http\Requests\Admin\UpdateAktivitasRequest;
use App\Models\Aktivitas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AktivitasController extends Controller
{
    public function index(IndexAktivitasRequest $request): Response
    {
        $search = $request->validated('search') ?? '';
        $query = Aktivitas::query()->withCount('tempatWisata');
        $this->search($query, $search);

        return Inertia::render('admin/aktivitas/index', [
            'items' => $query->orderBy('nama')->paginate(15)->appends(['search' => $search]),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StoreAktivitasRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->slug($data['nama']);
        Aktivitas::query()->create($data);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aktivitas berhasil ditambahkan.']);

        return back();
    }

    public function update(UpdateAktivitasRequest $request, Aktivitas $aktivitas): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->slug($data['nama'], $aktivitas->id);
        $aktivitas->update($data);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aktivitas berhasil diperbarui.']);

        return back();
    }

    public function destroy(Aktivitas $aktivitas): RedirectResponse
    {
        if ($aktivitas->tempatWisata()->exists()) {
            throw ValidationException::withMessages(['aktivitas' => 'Aktivitas masih digunakan oleh Tempat Wisata dan tidak dapat dihapus. Nonaktifkan jika tidak ingin menampilkannya.']);
        }

        $aktivitas->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Aktivitas berhasil dihapus.']);

        return back();
    }

    private function search(Builder $query, string $search): void
    {
        if ($search !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
            $query->whereRaw("nama LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
        }
    }

    private function slug(string $nama, ?int $except = null): string
    {
        $base = Str::slug($nama) ?: 'aktivitas';
        $slug = $base;
        $suffix = 2;
        while (Aktivitas::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except))->exists()) {
            $slug = Str::limit($base, 255 - strlen('-'.$suffix), '').'-'.$suffix++;
        }

        return $slug;
    }
}
