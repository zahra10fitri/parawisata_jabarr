<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Models\Destinasi;
use App\Models\Inquiry;
use App\Models\PaketPerjalanan;
use App\Models\Pengalaman;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function create(Request $request): Response
    {
        $paketId = $request->integer('paket');
        $selectedPackage = $paketId > 0 ? PaketPerjalanan::query()->where('status', true)->findOrFail($paketId) : null;

        return Inertia::render('inquiry/create', [
            'selectedPackage' => $selectedPackage?->only(['id', 'nama', 'slug', 'durasi_hari', 'harga_mulai']),
            'destinasiOptions' => Destinasi::query()->where('status', true)->orderBy('nama')->get(['id', 'nama']),
            'pengalamanOptions' => Pengalaman::query()->where('status', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($data): void {
            $inquiry = Inquiry::query()->create([
                ...Arr::except($data, ['destinasi', 'pengalaman']),
                'status' => Inquiry::STATUS_BARU,
            ]);
            $inquiry->destinasi()->sync($data['destinasi'] ?? []);
            $inquiry->pengalaman()->sync($data['pengalaman'] ?? []);
        });
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permintaan perjalanan berhasil dikirim. Tim kami akan segera menghubungi Anda.']);

        return to_route('inquiry.create', array_filter(['paket' => $data['paket_perjalanan_id'] ?? null]));
    }
}
