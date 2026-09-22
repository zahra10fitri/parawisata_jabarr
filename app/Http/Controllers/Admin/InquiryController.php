<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexInquiryRequest;
use App\Http\Requests\Admin\UpdateInquiryStatusRequest;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function index(IndexInquiryRequest $request): Response
    {
        $filters = ['search' => $request->validated('search') ?? '', 'status' => $request->validated('status') ?? ''];
        $query = Inquiry::query()->with('paketPerjalanan:id,nama,slug');
        if ($filters['search'] !== '') {
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']);
            $query->where(function (Builder $query) use ($escaped): void {
                $query->whereRaw("nama LIKE ? ESCAPE '!'", ['%'.$escaped.'%'])->orWhereRaw("email LIKE ? ESCAPE '!'", ['%'.$escaped.'%'])->orWhereRaw("whatsapp LIKE ? ESCAPE '!'", ['%'.$escaped.'%']);
            });
        }
        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return Inertia::render('admin/inquiry/index', ['inquiries' => $query->latest()->paginate(15)->withQueryString()->through(fn (Inquiry $inquiry): array => $this->summary($inquiry)), 'filters' => $filters, 'statusOptions' => Inquiry::STATUSES]);
    }

    public function show(Inquiry $inquiry): Response
    {
        $inquiry->load(['paketPerjalanan:id,nama,slug,durasi_hari,harga_mulai', 'destinasi:id,nama,slug', 'pengalaman:id,nama,slug']);

        return Inertia::render('admin/inquiry/show', ['inquiry' => [...$this->summary($inquiry), ...$inquiry->only(['catatan', 'created_at', 'updated_at']), 'destinasi' => $inquiry->destinasi->map->only(['id', 'nama', 'slug'])->all(), 'pengalaman' => $inquiry->pengalaman->map->only(['id', 'nama', 'slug'])->all()], 'statusOptions' => Inquiry::STATUSES]);
    }

    public function updateStatus(UpdateInquiryStatusRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $inquiry->update(['status' => $request->validated('status')]);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Status permintaan perjalanan berhasil diperbarui.']);

        return back();
    }

    /** @return array<string, mixed> */
    private function summary(Inquiry $inquiry): array
    {
        return [...$inquiry->only(['id', 'nama', 'email', 'whatsapp', 'jumlah_traveler', 'status', 'created_at']), 'tanggal_perjalanan' => $inquiry->tanggal_perjalanan->toDateString(), 'paket_perjalanan' => $inquiry->paketPerjalanan?->only(['id', 'nama', 'slug', 'durasi_hari', 'harga_mulai'])];
    }
}
