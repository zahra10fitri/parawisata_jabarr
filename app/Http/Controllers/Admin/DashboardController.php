<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\Inquiry;
use App\Models\PaketPerjalanan;
use App\Models\PesanKontak;
use App\Models\TempatWisata;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        /** @var Collection<string, int> $statusCounts */
        $statusCounts = Inquiry::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('dashboard', [
            'statistics' => [
                'destinasi' => Destinasi::query()->count(),
                'tempat_wisata' => TempatWisata::query()->count(),
                'paket_perjalanan' => PaketPerjalanan::query()->count(),
                'artikel' => Artikel::query()->count(),
                'inquiry' => Inquiry::query()->count(),
                'pesan_kontak' => PesanKontak::query()->count(),
            ],
            'recentInquiries' => Inquiry::query()->with('paketPerjalanan:id,nama')->latest()->latest('id')->limit(5)->get()->map(fn (Inquiry $inquiry): array => [
                ...$inquiry->only(['id', 'nama', 'status', 'created_at']),
                'tipe' => $inquiry->paket_perjalanan_id === null ? 'Custom' : 'Paket',
                'paket' => $inquiry->paketPerjalanan?->nama,
            ]),
            'recentMessages' => PesanKontak::query()->latest()->latest('id')->limit(5)->get(['id', 'nama', 'subjek', 'dibaca_pada', 'created_at']),
            'inquiryStatusCounts' => collect(Inquiry::STATUSES)->map(fn (string $status): array => ['status' => $status, 'total' => (int) ($statusCounts->get($status) ?? 0)])->values(),
        ]);
    }
}
