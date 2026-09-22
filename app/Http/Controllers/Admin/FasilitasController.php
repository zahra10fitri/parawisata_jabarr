<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexFasilitasRequest;
use App\Http\Requests\Admin\StoreFasilitasRequest;
use App\Http\Requests\Admin\UpdateFasilitasRequest;
use App\Models\Fasilitas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FasilitasController extends Controller
{
    public function index(IndexFasilitasRequest $request): Response
    {
        $search = $request->validated('search') ?? '';
        $query = Fasilitas::query()->withCount('tempatWisata');
        $this->search($query, $search);

        return Inertia::render('admin/fasilitas/index', [
            'items' => $query->orderBy('nama')->paginate(15)->appends(['search' => $search]),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StoreFasilitasRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->slug($data['nama']);
        Fasilitas::query()->create($data);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Fasilitas berhasil ditambahkan.']);

        return back();
    }

    public function update(UpdateFasilitasRequest $request, Fasilitas $fasilitas): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->slug($data['nama'], $fasilitas->id);
        $fasilitas->update($data);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Fasilitas berhasil diperbarui.']);

        return back();
    }

    public function destroy(Fasilitas $fasilitas): RedirectResponse
    {
        if ($fasilitas->tempatWisata()->exists()) {
            throw ValidationException::withMessages(['fasilitas' => 'Fasilitas masih digunakan oleh Tempat Wisata dan tidak dapat dihapus. Nonaktifkan jika tidak ingin menampilkannya.']);
        }

        $fasilitas->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Fasilitas berhasil dihapus.']);

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
        $base = Str::slug($nama) ?: 'fasilitas';
        $slug = $base;
        $suffix = 2;
        while (Fasilitas::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except))->exists()) {
            $slug = Str::limit($base, 255 - strlen('-'.$suffix), '').'-'.$suffix++;
        }

        return $slug;
    }
}
