<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexPesanKontakRequest;
use App\Models\PesanKontak;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PesanKontakController extends Controller
{
    public function index(IndexPesanKontakRequest $request): Response
    {
        $filters = ['search' => $request->validated('search') ?? '', 'status' => $request->validated('status') ?? ''];
        $query = PesanKontak::query();
        if ($filters['search'] !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']);
            $query->where(function (Builder $query) use ($escaped): void {
                $query->whereRaw("nama LIKE ? ESCAPE '!'", ['%'.$escaped.'%'])->orWhereRaw("email LIKE ? ESCAPE '!'", ['%'.$escaped.'%'])->orWhereRaw("subjek LIKE ? ESCAPE '!'", ['%'.$escaped.'%'])->orWhereRaw("pesan LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
            });
        }
        if ($filters['status'] === 'belum_dibaca') {
            $query->whereNull('dibaca_pada');
        } elseif ($filters['status'] === 'sudah_dibaca') {
            $query->whereNotNull('dibaca_pada');
        }

        return Inertia::render('admin/pesan-kontak/index', ['pesanKontak' => $query->latest()->paginate(15)->withQueryString(), 'filters' => $filters]);
    }

    public function show(PesanKontak $pesanKontak): Response
    {
        if ($pesanKontak->dibaca_pada === null) {
            $pesanKontak->update(['dibaca_pada' => now()]);
        }

        return Inertia::render('admin/pesan-kontak/show', ['pesanKontak' => $pesanKontak->fresh()]);
    }

    public function toggleRead(PesanKontak $pesanKontak): RedirectResponse
    {
        $pesanKontak->update(['dibaca_pada' => $pesanKontak->dibaca_pada === null ? now() : null]);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Status pesan berhasil diperbarui.']);

        return back();
    }

    public function destroy(PesanKontak $pesanKontak): RedirectResponse
    {
        $pesanKontak->delete();
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pesan kontak berhasil dihapus.']);

        return to_route('admin.pesan-kontak.index');
    }
}
