<?php

use App\Models\Destinasi;
use App\Models\Inquiry;
use App\Models\PaketPerjalanan;
use App\Models\Pengalaman;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

function inquiryInput(): array
{
    return ['nama' => 'Siti Aminah', 'email' => 'siti@example.com', 'whatsapp' => '+62 812-3456-7890', 'tanggal_perjalanan' => now()->addMonth()->toDateString(), 'jumlah_traveler' => 4, 'catatan' => 'Membawa anak kecil'];
}

test('package inquiry form automatically selects an active package', function (): void {
    $paket = PaketPerjalanan::factory()->create(['status' => true]);

    $this->get(route('inquiry.create', ['paket' => $paket->id]))->assertInertia(fn (Assert $page) => $page
        ->component('inquiry/create')->where('selectedPackage.id', $paket->id)->where('selectedPackage.nama', $paket->nama));
});

test('public can submit an inquiry for a package with new status', function (): void {
    $paket = PaketPerjalanan::factory()->create(['status' => true]);

    $this->post(route('inquiry.store'), [...inquiryInput(), 'paket_perjalanan_id' => $paket->id, 'status' => 'SELESAI'])
        ->assertSessionHasErrors('status');
    $this->post(route('inquiry.store'), [...inquiryInput(), 'paket_perjalanan_id' => $paket->id])
        ->assertSessionHasNoErrors()->assertRedirect(route('inquiry.create', ['paket' => $paket->id]))->assertInertiaFlash('toast.type', 'success');

    $this->assertDatabaseHas('inquiry', ['paket_perjalanan_id' => $paket->id, 'nama' => 'Siti Aminah', 'status' => Inquiry::STATUS_BARU]);
});

test('general trip plan stores destination and experience interests', function (): void {
    $destinasi = Destinasi::factory()->create(['status' => true]);
    $pengalaman = Pengalaman::factory()->create(['status' => true]);

    $this->post(route('inquiry.store'), [...inquiryInput(), 'destinasi' => [$destinasi->id], 'pengalaman' => [$pengalaman->id]])->assertSessionHasNoErrors();
    $inquiry = Inquiry::query()->sole();
    expect($inquiry->paket_perjalanan_id)->toBeNull();
    $this->assertDatabaseHas('destinasi_inquiry', ['inquiry_id' => $inquiry->id, 'destinasi_id' => $destinasi->id]);
    $this->assertDatabaseHas('inquiry_pengalaman', ['inquiry_id' => $inquiry->id, 'pengalaman_id' => $pengalaman->id]);
});

test('inquiry rejects invalid contact travel and inactive selections', function (array $changes, string $field): void {
    $this->post(route('inquiry.store'), [...inquiryInput(), ...$changes])->assertSessionHasErrors($field);
    $this->assertDatabaseCount('inquiry', 0);
})->with([
    'invalid email' => [['email' => 'not-an-email'], 'email'],
    'invalid whatsapp' => [['whatsapp' => 'abc'], 'whatsapp'],
    'past date' => [['tanggal_perjalanan' => now()->subDay()->toDateString()], 'tanggal_perjalanan'],
    'zero travelers' => [['jumlah_traveler' => 0], 'jumlah_traveler'],
    'inactive package' => fn () => [['paket_perjalanan_id' => PaketPerjalanan::factory()->create(['status' => false])->id], 'paket_perjalanan_id'],
    'inactive destination' => fn () => [['destinasi' => [Destinasi::factory()->create(['status' => false])->id]], 'destinasi.0'],
]);
