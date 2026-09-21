<?php

use App\Models\Itinerary;
use App\Models\KegiatanItinerary;
use App\Models\PaketPerjalanan;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
    $this->actingAs(User::factory()->admin()->create());
});

test('admin manages days and activities for a package', function (): void {
    $paket = PaketPerjalanan::factory()->create(['status' => true]);
    $this->post(route('admin.paket-perjalanan.itinerary.store', $paket), ['hari_ke' => 1, 'judul' => 'Bandung', 'urutan' => 0])->assertSessionHasNoErrors();
    $hari = Itinerary::query()->sole();
    $this->post(route('admin.paket-perjalanan.itinerary.kegiatan.store', [$paket, $hari]), ['judul' => 'Sarapan', 'keterangan' => 'Kuliner lokal', 'waktu' => '08:30', 'urutan' => 0])->assertSessionHasNoErrors();
    $kegiatan = KegiatanItinerary::query()->sole();
    $this->put(route('admin.paket-perjalanan.itinerary.kegiatan.update', [$paket, $hari, $kegiatan]), ['judul' => 'Sarapan pagi', 'waktu' => '08:00', 'urutan' => 1])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('kegiatan_itinerary', ['judul' => 'Sarapan pagi', 'waktu' => '08:00', 'urutan' => 1]);
});

test('admin can reorder days and activities', function (): void {
    $paket = PaketPerjalanan::factory()->create();
    $first = $paket->itinerary()->create(['hari_ke' => 1, 'urutan' => 0]);
    $second = $paket->itinerary()->create(['hari_ke' => 2, 'urutan' => 1]);
    $one = $first->kegiatan()->create(['judul' => 'Satu', 'urutan' => 0]);
    $two = $first->kegiatan()->create(['judul' => 'Dua', 'urutan' => 1]);
    $this->put(route('admin.paket-perjalanan.itinerary.reorder', $paket), ['ids' => [$second->id, $first->id]])->assertSessionHasNoErrors();
    $this->put(route('admin.paket-perjalanan.itinerary.kegiatan.reorder', [$paket, $first]), ['ids' => [$two->id, $one->id]])->assertSessionHasNoErrors();
    expect($second->fresh()->urutan)->toBe(0)->and($two->fresh()->urutan)->toBe(0);
});

test('public package detail exposes ordered itinerary', function (): void {
    $paket = PaketPerjalanan::factory()->create(['status' => true]);
    $later = $paket->itinerary()->create(['hari_ke' => 2, 'urutan' => 2]);
    $first = $paket->itinerary()->create(['hari_ke' => 1, 'urutan' => 0]);
    $first->kegiatan()->create(['judul' => 'Siang', 'waktu' => '12:00', 'urutan' => 2]);
    $first->kegiatan()->create(['judul' => 'Pagi', 'waktu' => '08:00', 'urutan' => 0]);
    $later->kegiatan()->create(['judul' => 'Pulang', 'urutan' => 0]);
    $this->get(route('paket-perjalanan.show', $paket->slug))->assertInertia(fn (Assert $page) => $page->component('paket-perjalanan/show')->where('paketPerjalanan.itinerary.0.hari_ke', 1)->where('paketPerjalanan.itinerary.0.kegiatan.0.judul', 'Pagi')->where('paketPerjalanan.itinerary.1.hari_ke', 2));
});

test('nested records from other packages return not found', function (): void {
    $paket = PaketPerjalanan::factory()->create();
    $other = PaketPerjalanan::factory()->create();
    $hari = $other->itinerary()->create(['hari_ke' => 1, 'urutan' => 0]);
    $this->delete(route('admin.paket-perjalanan.itinerary.destroy', [$paket, $hari]))->assertNotFound();
    $this->assertModelExists($hari);
});
