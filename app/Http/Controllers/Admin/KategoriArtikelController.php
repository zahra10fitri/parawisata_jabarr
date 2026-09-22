<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKategoriArtikelRequest;
use App\Http\Requests\Admin\UpdateKategoriArtikelRequest;
use App\Models\KategoriArtikel;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class KategoriArtikelController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/kategori-artikel/index', ['kategori' => KategoriArtikel::query()->withCount('artikel')->orderBy('nama')->get()]);
    }

    public function store(StoreKategoriArtikelRequest $request): RedirectResponse
    {
        $nama = $request->validated('nama');
        KategoriArtikel::query()->create(['nama' => $nama, 'slug' => $this->slug($nama)]);

        return back();
    }

    public function update(UpdateKategoriArtikelRequest $request, KategoriArtikel $kategoriArtikel): RedirectResponse
    {
        $nama = $request->validated('nama');
        $kategoriArtikel->update(['nama' => $nama, 'slug' => $this->slug($nama, $kategoriArtikel->id)]);

        return back();
    }

    public function destroy(KategoriArtikel $kategoriArtikel): RedirectResponse
    {
        try {
            $kategoriArtikel->delete();
        } catch (QueryException) {
            throw ValidationException::withMessages(['kategori' => 'Kategori masih digunakan oleh artikel dan tidak dapat dihapus.']);
        }

return back();
    }

    private function slug(string $nama, ?int $except = null): string
    {
        $base = Str::slug($nama) ?: 'kategori';
        $slug = $base;
        $i = 2;
        while (KategoriArtikel::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except))->exists()) {
            $slug = Str::limit($base, 255 - strlen('-'.$i), '').'-'.$i++;
        }

return $slug;
    }
}
