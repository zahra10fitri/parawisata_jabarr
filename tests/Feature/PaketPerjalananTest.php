<?php

use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\TempatWisata;
use App\Models\User;

beforeEach(function (): void {
    $this->withoutVite();
});

test('admins can create packages with destinations and attractions', function (): void {
    $destinasi = Destinasi::factory()->create();
    $wisata = TempatWisata::factory()->for($destinasi, 'destinasi')->create();
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.paket-perjalanan.store'), ['nama' => 'Jelajah Priangan', 'durasi_hari' => 3, 'harga_mulai' => 750000, 'status' => true, 'unggulan' => true, 'urutan' => 1, 'destinasi' => [$destinasi->id], 'tempat_wisata' => [$wisata->id]])->assertSessionHasNoErrors();
    $paket = PaketPerjalanan::query()->sole();
    expect($paket->slug)->toBe('jelajah-priangan');
    $this->assertDatabaseHas('destinasi_paket_perjalanan', ['paket_perjalanan_id' => $paket->id, 'destinasi_id' => $destinasi->id]);
});

test('public package list only includes active packages', function (): void {
    $active = PaketPerjalanan::factory()->create(['status' => true]);
    PaketPerjalanan::factory()->create(['status' => false]);
    $this->get(route('paket-perjalanan.index'))->assertInertia(fn ($page) => $page->component('paket-perjalanan/index')->has('paketPerjalanan.data', 1)->where('paketPerjalanan.data.0.id', $active->id));
});
