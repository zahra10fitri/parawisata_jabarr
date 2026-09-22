<?php

use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\Inquiry;
use App\Models\PaketPerjalanan;
use App\Models\PesanKontak;
use App\Models\TempatWisata;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated admins can visit the dashboard', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboard'));
});

test('nonadmins cannot visit the dashboard', function (): void {
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertForbidden();
});

test('dashboard shows real statistics recent activity and inquiry status totals', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $destinasi = Destinasi::factory()->create();
    TempatWisata::factory()->count(2)->create(['destinasi_id' => $destinasi->id]);
    $paket = PaketPerjalanan::factory()->create();
    Artikel::factory()->count(3)->create();
    Inquiry::query()->create(['paket_perjalanan_id' => $paket->id, 'nama' => 'Pelanggan Paket', 'email' => 'paket@example.com', 'whatsapp' => '081234567890', 'tanggal_perjalanan' => now()->addMonth(), 'jumlah_traveler' => 2, 'status' => 'BARU']);
    Inquiry::query()->create(['nama' => 'Pelanggan Custom', 'email' => 'custom@example.com', 'whatsapp' => '081234567891', 'tanggal_perjalanan' => now()->addMonths(2), 'jumlah_traveler' => 4, 'status' => 'DIPROSES']);
    PesanKontak::factory()->create(['nama' => 'Pesan Dibaca', 'dibaca_pada' => now()->subMinute(), 'created_at' => now()->subHour()]);
    PesanKontak::factory()->create(['nama' => 'Pesan Baru', 'subjek' => 'Pertanyaan terbaru', 'dibaca_pada' => null, 'created_at' => now()]);

    $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page
        ->component('dashboard')
        ->where('statistics.destinasi', 1)
        ->where('statistics.tempat_wisata', 2)
        ->where('statistics.paket_perjalanan', 1)
        ->where('statistics.artikel', 3)
        ->where('statistics.inquiry', 2)
        ->where('statistics.pesan_kontak', 2)
        ->has('recentInquiries', 2)
        ->where('recentInquiries.0.nama', 'Pelanggan Custom')
        ->where('recentInquiries.0.tipe', 'Custom')
        ->where('recentInquiries.1.tipe', 'Paket')
        ->where('recentInquiries.1.paket', $paket->nama)
        ->has('recentMessages', 2)
        ->where('recentMessages.0.nama', 'Pesan Baru')
        ->where('recentMessages.0.dibaca_pada', null)
        ->has('inquiryStatusCounts', count(Inquiry::STATUSES))
        ->where('inquiryStatusCounts.0', ['status' => 'BARU', 'total' => 1])
        ->where('inquiryStatusCounts.1', ['status' => 'DIPROSES', 'total' => 1])
        ->where('inquiryStatusCounts.2', ['status' => 'PENAWARAN DIKIRIM', 'total' => 0]));
});

test('dashboard limits recent inquiry and contact summaries to five records', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    foreach (range(1, 6) as $number) {
        Inquiry::query()->create(['nama' => 'Customer '.$number, 'email' => "customer{$number}@example.com", 'whatsapp' => '081234567890', 'tanggal_perjalanan' => now()->addMonth(), 'jumlah_traveler' => 2, 'status' => 'BARU']);
    }
    PesanKontak::factory()->count(6)->create();

    $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->has('recentInquiries', 5)->has('recentMessages', 5));
});
