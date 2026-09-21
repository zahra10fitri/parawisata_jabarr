<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderItineraryRequest;
use App\Http\Requests\Admin\ReorderKegiatanItineraryRequest;
use App\Http\Requests\Admin\StoreItineraryRequest;
use App\Http\Requests\Admin\StoreKegiatanItineraryRequest;
use App\Http\Requests\Admin\UpdateItineraryRequest;
use App\Http\Requests\Admin\UpdateKegiatanItineraryRequest;
use App\Models\Itinerary;
use App\Models\KegiatanItinerary;
use App\Models\PaketPerjalanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ItineraryController extends Controller
{
    public function index(PaketPerjalanan $paketPerjalanan): Response
    {
        $paketPerjalanan->load('itinerary.kegiatan');

        return Inertia::render('admin/paket-perjalanan/itinerary', [
            'paketPerjalanan' => $paketPerjalanan->only(['id', 'nama', 'slug', 'durasi_hari']),
            'itinerary' => $paketPerjalanan->itinerary->map(fn (Itinerary $hari): array => [
                ...$hari->only(['id', 'hari_ke', 'judul', 'urutan']),
                'kegiatan' => $hari->kegiatan->map->only(['id', 'judul', 'keterangan', 'waktu', 'urutan'])->all(),
            ])->all(),
        ]);
    }

    public function store(StoreItineraryRequest $request, PaketPerjalanan $paketPerjalanan): RedirectResponse
    {
        $this->ensureUniqueDay($paketPerjalanan, (int) $request->validated('hari_ke'));
        $paketPerjalanan->itinerary()->create($request->validated());

        return back();
    }

    public function update(UpdateItineraryRequest $request, PaketPerjalanan $paketPerjalanan, Itinerary $itinerary): RedirectResponse
    {
        $this->guardDay($paketPerjalanan, $itinerary);
        $this->ensureUniqueDay($paketPerjalanan, (int) $request->validated('hari_ke'), $itinerary);
        $itinerary->update($request->validated());

        return back();
    }

    public function destroy(PaketPerjalanan $paketPerjalanan, Itinerary $itinerary): RedirectResponse
    {
        $this->guardDay($paketPerjalanan, $itinerary);
        $itinerary->delete();

        return back();
    }

    public function reorder(ReorderItineraryRequest $request, PaketPerjalanan $paketPerjalanan): RedirectResponse
    {
        $ids = $request->validated('ids');
        if ($paketPerjalanan->itinerary()->whereIn('id', $ids)->count() !== count($ids) || $paketPerjalanan->itinerary()->count() !== count($ids)) {
            throw ValidationException::withMessages(['ids' => 'Daftar hari sudah berubah. Muat ulang halaman.']);
        }
        DB::transaction(fn () => collect($ids)->each(fn (int $id, int $index) => Itinerary::whereKey($id)->update(['urutan' => $index])));

        return back();
    }

    public function storeKegiatan(StoreKegiatanItineraryRequest $request, PaketPerjalanan $paketPerjalanan, Itinerary $itinerary): RedirectResponse
    {
        $this->guardDay($paketPerjalanan, $itinerary);
        $itinerary->kegiatan()->create($request->validated());

        return back();
    }

    public function updateKegiatan(UpdateKegiatanItineraryRequest $request, PaketPerjalanan $paketPerjalanan, Itinerary $itinerary, KegiatanItinerary $kegiatan): RedirectResponse
    {
        $this->guardActivity($paketPerjalanan, $itinerary, $kegiatan);
        $kegiatan->update($request->validated());

        return back();
    }

    public function destroyKegiatan(PaketPerjalanan $paketPerjalanan, Itinerary $itinerary, KegiatanItinerary $kegiatan): RedirectResponse
    {
        $this->guardActivity($paketPerjalanan, $itinerary, $kegiatan);
        $kegiatan->delete();

        return back();
    }

    public function reorderKegiatan(ReorderKegiatanItineraryRequest $request, PaketPerjalanan $paketPerjalanan, Itinerary $itinerary): RedirectResponse
    {
        $this->guardDay($paketPerjalanan, $itinerary);
        $ids = $request->validated('ids');
        if ($itinerary->kegiatan()->whereIn('id', $ids)->count() !== count($ids) || $itinerary->kegiatan()->count() !== count($ids)) {
            throw ValidationException::withMessages(['ids' => 'Daftar kegiatan sudah berubah. Muat ulang halaman.']);
        }
        DB::transaction(fn () => collect($ids)->each(fn (int $id, int $index) => KegiatanItinerary::whereKey($id)->update(['urutan' => $index])));

        return back();
    }

    private function guardDay(PaketPerjalanan $paket, Itinerary $itinerary): void
    {
        abort_unless($itinerary->paket_perjalanan_id === $paket->id, 404);
    }

    private function guardActivity(PaketPerjalanan $paket, Itinerary $itinerary, KegiatanItinerary $kegiatan): void
    {
        $this->guardDay($paket, $itinerary);
        abort_unless($kegiatan->itinerary_id === $itinerary->id, 404);
    }

    private function ensureUniqueDay(PaketPerjalanan $paket, int $day, ?Itinerary $except = null): void
    {
        if ($paket->itinerary()->where('hari_ke', $day)->when($except !== null, fn ($query) => $query->whereKeyNot($except->id))->exists()) {
            throw ValidationException::withMessages(['hari_ke' => 'Hari tersebut sudah ada dalam itinerary.']);
        }
    }
}
