<?php

use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\TempatWisata;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('global search groups active matching content from every module', function (): void {
    $destinasi = Destinasi::factory()->create(['nama' => 'Bandung Raya', 'status' => true]);
    $wisata = TempatWisata::factory()->create(['nama' => 'Kawah Bandung', 'status' => true]);
    $paket = PaketPerjalanan::factory()->create(['nama' => 'Eksplorasi Bandung', 'status' => true]);
    $artikel = Artikel::factory()->create(['judul' => 'Panduan Bandung', 'status' => true, 'tanggal_publikasi' => now()->subHour()]);

    $this->get(route('pencarian.index', ['q' => 'Bandung']))->assertInertia(fn (Assert $page) => $page->component('pencarian/index')->where('query', 'Bandung')->where('results.destinasi.0.title', $destinasi->nama)->where('results.wisata.0.title', $wisata->nama)->where('results.paket.0.title', $paket->nama)->where('results.artikel.0.title', $artikel->judul));
});

test('global search matches relevant descriptive information', function (): void {
    $destinasi = Destinasi::factory()->create(['nama' => 'Priangan', 'lokasi' => 'Bandung Selatan', 'status' => true]);
    TempatWisata::factory()->create(['nama' => 'Curug', 'deskripsi_singkat' => 'Air terjun tersembunyi', 'status' => true]);
    PaketPerjalanan::factory()->create(['nama' => 'Liburan', 'highlight' => 'Menyusuri perkebunan teh', 'status' => true]);
    Artikel::factory()->create(['judul' => 'Catatan', 'ringkasan' => 'Cerita perkebunan teh', 'status' => true, 'tanggal_publikasi' => now()->subHour()]);

    $this->get(route('pencarian.index', ['q' => 'Bandung Selatan']))->assertInertia(fn (Assert $page) => $page->where('results.destinasi.0.title', $destinasi->nama));
    $this->get(route('pencarian.index', ['q' => 'perkebunan teh']))->assertInertia(fn (Assert $page) => $page->has('results.paket', 1)->has('results.artikel', 1));
});

test('global search excludes inactive drafts and future articles', function (): void {
    Destinasi::factory()->create(['nama' => 'Rahasia', 'status' => false]);
    TempatWisata::factory()->create(['nama' => 'Rahasia', 'status' => false]);
    PaketPerjalanan::factory()->create(['nama' => 'Rahasia', 'status' => false]);
    Artikel::factory()->create(['judul' => 'Rahasia', 'status' => false, 'tanggal_publikasi' => now()->subDay()]);
    Artikel::factory()->create(['judul' => 'Rahasia', 'status' => true, 'tanggal_publikasi' => now()->addDay()]);

    $this->get(route('pencarian.index', ['q' => 'Rahasia']))->assertInertia(fn (Assert $page) => $page->has('results.destinasi', 0)->has('results.wisata', 0)->has('results.paket', 0)->has('results.artikel', 0));
});

test('empty and wildcard queries are handled safely', function (): void {
    Destinasi::factory()->create(['nama' => 'Wisata 100%', 'status' => true]);
    Destinasi::factory()->create(['nama' => 'Wisata biasa', 'status' => true]);
    $this->get(route('pencarian.index'))->assertInertia(fn (Assert $page) => $page->where('query', '')->has('results.destinasi', 0));
    $this->get(route('pencarian.index', ['q' => '%']))->assertInertia(fn (Assert $page) => $page->has('results.destinasi', 1)->where('results.destinasi.0.title', 'Wisata 100%'));
});
