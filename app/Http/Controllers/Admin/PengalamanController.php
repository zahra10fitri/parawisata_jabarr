<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPengalamanRequest;
use App\Http\Requests\Admin\StorePengalamanRequest;
use App\Http\Requests\Admin\UpdatePengalamanRequest;
use App\Models\Pengalaman;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PengalamanController extends Controller
{
    public function index(IndexPengalamanRequest $request): Response
    {
        $search = $request->validated('search') ?? '';
        $query = Pengalaman::query()->withCount(['tempatWisata', 'inquiry']);
        $this->search($query, $search);

        return Inertia::render('admin/pengalaman/index', [
            'items' => $query->orderBy('nama')->paginate(15)->appends(['search' => $search])
                ->through(fn (Pengalaman $pengalaman): array => [
                    ...$pengalaman->toArray(),
                    'foto_url' => $pengalaman->ikon_atau_gambar === null ? null : Storage::disk('public')->url($pengalaman->ikon_atau_gambar),
                ]),
            'filters' => ['search' => $search],
        ]);
    }

    public function store(StorePengalamanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->slug($data['nama']);
        $data['ikon_atau_gambar'] = $this->storePhoto($data['ikon_atau_gambar'] ?? null);
        Pengalaman::query()->create($data);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pengalaman berhasil ditambahkan.']);

        return back();
    }

    public function update(UpdatePengalamanRequest $request, Pengalaman $pengalaman): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $this->slug($data['nama'], $pengalaman->id);
        $photo = $data['ikon_atau_gambar'] ?? null;
        if ($photo !== null) {
            if ($pengalaman->ikon_atau_gambar !== null) {
                Storage::disk('public')->delete($pengalaman->ikon_atau_gambar);
            }
            $data['ikon_atau_gambar'] = $this->storePhoto($photo);
        } else {
            unset($data['ikon_atau_gambar']);
        }
        $pengalaman->update($data);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pengalaman berhasil diperbarui.']);

        return back();
    }

    public function destroy(Pengalaman $pengalaman): RedirectResponse
    {
        if ($pengalaman->tempatWisata()->exists() || $pengalaman->inquiry()->exists()) {
            throw ValidationException::withMessages(['pengalaman' => 'Pengalaman masih digunakan dan tidak dapat dihapus. Nonaktifkan jika tidak ingin menampilkannya.']);
        }

        if ($pengalaman->ikon_atau_gambar !== null) {
            Storage::disk('public')->delete($pengalaman->ikon_atau_gambar);
        }
        $pengalaman->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pengalaman berhasil dihapus.']);

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
        $base = Str::slug($nama) ?: 'pengalaman';
        $slug = $base;
        $suffix = 2;
        while (Pengalaman::query()->where('slug', $slug)->when($except, fn ($query) => $query->whereKeyNot($except))->exists()) {
            $slug = Str::limit($base, 255 - strlen('-'.$suffix), '').'-'.$suffix++;
        }

        return $slug;
    }

    private function storePhoto(?UploadedFile $photo): ?string
    {
        return $photo?->store('pengalaman', 'public');
    }
}
