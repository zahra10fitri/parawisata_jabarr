<?php

use App\Models\Destinasi;
use App\Models\Inquiry;
use App\Models\PaketPerjalanan;
use App\Models\Pengalaman;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

function createAdminInquiry(array $attributes = []): Inquiry
{
    return Inquiry::query()->create([...['nama' => 'Siti Aminah', 'email' => 'siti@example.com', 'whatsapp' => '+628123456789', 'tanggal_perjalanan' => now()->addMonth()->toDateString(), 'jumlah_traveler' => 4, 'catatan' => 'Perjalanan keluarga', 'status' => Inquiry::STATUS_BARU], ...$attributes]);
}

test('only admins can access inquiry management routes', function (string $method, string $uri, int $expected): void {
    if (str_contains($uri, '/1')) {
        createAdminInquiry();
    }
    if ($expected === 403) {
        $this->actingAs(User::factory()->create());
    }
    $this->call($method, $uri)->assertStatus($expected);
})->with([
    'guest list' => ['GET', '/admin/permintaan-perjalanan', 302],
    'user list' => ['GET', '/admin/permintaan-perjalanan', 403],
    'guest detail' => ['GET', '/admin/permintaan-perjalanan/1', 302],
    'user update' => ['PATCH', '/admin/permintaan-perjalanan/1/status', 403],
]);

test('admin list searches filters and paginates inquiries', function (): void {
    $matches = collect(range(1, 16))->map(fn (int $index): Inquiry => createAdminInquiry(['nama' => 'Siti '.$index, 'email' => "siti{$index}@example.com", 'status' => 'DIPROSES', 'created_at' => now()->subMinutes($index)]));
    createAdminInquiry(['nama' => 'Siti Baru', 'status' => 'BARU']);
    createAdminInquiry(['nama' => 'Budi', 'email' => 'budi@example.com', 'status' => 'DIPROSES']);

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.inquiry.index', ['search' => 'Siti', 'status' => 'DIPROSES']))->assertInertia(fn (Assert $page) => $page
        ->component('admin/inquiry/index')->has('inquiries.data', 15)->where('inquiries.total', 16)
        ->where('inquiries.next_page_url', fn (string $url): bool => str_contains($url, 'search=Siti') && str_contains($url, 'status=DIPROSES'))
        ->where('inquiries.data.0.id', $matches->last()->id));
});

test('detail displays package or custom inquiry relations', function (): void {
    $paket = PaketPerjalanan::factory()->create();
    $packageInquiry = createAdminInquiry(['paket_perjalanan_id' => $paket->id]);
    $customInquiry = createAdminInquiry(['nama' => 'Custom']);
    $destinasi = Destinasi::factory()->create();
    $pengalaman = Pengalaman::factory()->create();
    $customInquiry->destinasi()->attach($destinasi);
    $customInquiry->pengalaman()->attach($pengalaman);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.inquiry.show', $packageInquiry))->assertInertia(fn (Assert $page) => $page->component('admin/inquiry/show')->where('inquiry.paket_perjalanan.id', $paket->id)->has('inquiry.destinasi', 0));
    $this->actingAs($admin)->get(route('admin.inquiry.show', $customInquiry))->assertInertia(fn (Assert $page) => $page->where('inquiry.paket_perjalanan', null)->where('inquiry.destinasi.0.id', $destinasi->id)->where('inquiry.pengalaman.0.id', $pengalaman->id)->where('inquiry.catatan', 'Perjalanan keluarga'));
});

test('admin can update inquiry status only to supported values', function (): void {
    $inquiry = createAdminInquiry();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch(route('admin.inquiry.status.update', $inquiry), ['status' => 'PENAWARAN DIKIRIM'])->assertSessionHasNoErrors()->assertInertiaFlash('toast.type', 'success');
    expect($inquiry->fresh()->status)->toBe('PENAWARAN DIKIRIM');
    $this->actingAs($admin)->patch(route('admin.inquiry.status.update', $inquiry), ['status' => 'STATUS PALSU'])->assertSessionHasErrors('status');
    expect($inquiry->fresh()->status)->toBe('PENAWARAN DIKIRIM');
});

test('invalid list filters are rejected', function (): void {
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.inquiry.index', ['status' => 'INVALID', 'page' => 0]))->assertSessionHasErrors(['status', 'page']);
});
