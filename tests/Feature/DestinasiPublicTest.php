<?php

use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\TempatWisata;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('public destination detail exposes its content and only active related records', function (): void {
    $disk = Storage::fake('public');
    $destinasi = Destinasi::factory()->aktif()->create([
        'nama' => 'Bandung Selatan', 'slug' => 'bandung-selatan', 'foto_utama' => 'destinasi/bandung.jpg',
        'deskripsi_singkat' => 'Hamparan alam Priangan.', 'deskripsi_lengkap' => 'Cerita lengkap Bandung Selatan.',
        'lokasi' => 'Kabupaten Bandung', 'waktu_terbaik' => 'Musim kemarau', 'cara_menuju' => 'Melalui Soreang',
        'transportasi_lokal' => 'Angkutan lokal', 'tips' => 'Bawa jaket',
    ]);
    $wisata = TempatWisata::factory()->aktif()->for($destinasi, 'destinasi')->create(['nama' => 'Kawah Putih', 'foto_utama' => 'tempat-wisata/kawah.jpg']);
    TempatWisata::factory()->for($destinasi, 'destinasi')->create(['nama' => 'Wisata Rahasia']);
    $paket = PaketPerjalanan::factory()->create(['nama' => 'Jelajah Ciwidey', 'status' => true, 'foto_utama' => 'paket-perjalanan/ciwidey.jpg']);
    $inactivePackage = PaketPerjalanan::factory()->create(['status' => false]);
    $destinasi->paketPerjalanan()->attach([$paket->id, $inactivePackage->id]);

    $this->get(route('destinasi.show', $destinasi->slug))->assertInertia(fn (Assert $page) => $page
        ->component('destinasi/show')->where('destinasi.nama', 'Bandung Selatan')->where('destinasi.foto_url', $disk->url('destinasi/bandung.jpg'))
        ->where('destinasi.deskripsi_lengkap', 'Cerita lengkap Bandung Selatan.')->where('destinasi.lokasi', 'Kabupaten Bandung')
        ->where('destinasi.waktu_terbaik', 'Musim kemarau')->where('destinasi.cara_menuju', 'Melalui Soreang')
        ->where('destinasi.transportasi_lokal', 'Angkutan lokal')->where('destinasi.tips', 'Bawa jaket')
        ->has('destinasi.tempat_wisata', 1)->where('destinasi.tempat_wisata.0.id', $wisata->id)
        ->has('destinasi.paket_perjalanan', 1)->where('destinasi.paket_perjalanan.0.id', $paket->id)
        ->missing('destinasi.status')->missing('destinasi.foto_utama'));
});

test('inactive destination cannot be opened publicly even by admin', function (): void {
    $destinasi = Destinasi::factory()->create(['slug' => 'destinasi-rahasia', 'status' => false]);

    $this->get(route('destinasi.show', $destinasi->slug))->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())->get(route('destinasi.show', $destinasi->slug))->assertNotFound();
});

test('destination detail resolves slug only and supports empty optional relations', function (): void {
    $destinasi = Destinasi::factory()->aktif()->create(['slug' => 'garut']);

    $this->get(route('destinasi.show', $destinasi->slug))->assertInertia(fn (Assert $page) => $page
        ->where('destinasi.foto_url', null)->has('destinasi.tempat_wisata', 0)->has('destinasi.paket_perjalanan', 0));
    $this->get(route('destinasi.show', (string) $destinasi->id))->assertNotFound();
    $this->get(route('destinasi.show', 'tidak-ada'))->assertNotFound();
});

test('homepage and global search point destinations to public detail', function (): void {
    $destinasi = Destinasi::factory()->aktif()->create(['nama' => 'Cirebon Pesisir', 'slug' => 'cirebon-pesisir']);

    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->where('destinasi.0.slug', 'cirebon-pesisir'));
    $this->get(route('pencarian.index', ['q' => 'Cirebon Pesisir']))->assertInertia(fn (Assert $page) => $page->where('results.destinasi.0.url', '/destinasi/cirebon-pesisir'));
});
