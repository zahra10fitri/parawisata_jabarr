<?php

use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('homepage serves active dynamic content from every public module', function (): void {
    $destinasi = Destinasi::factory()->create(['status' => true, 'unggulan' => true]);
    $pengalaman = Pengalaman::factory()->create(['status' => true]);
    $wisata = TempatWisata::factory()->for($destinasi, 'destinasi')->create(['status' => true, 'unggulan' => true]);
    $paket = PaketPerjalanan::factory()->create(['status' => true, 'unggulan' => true]);
    $paket->destinasi()->attach($destinasi);
    $artikel = Artikel::factory()->create(['status' => true, 'unggulan' => true, 'tanggal_publikasi' => now()->subHour()]);

    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->component('welcome')
        ->where('destinasi.0.id', $destinasi->id)->where('pengalaman.0.id', $pengalaman->id)
        ->where('wisata.0.id', $wisata->id)->where('paket.0.id', $paket->id)->where('artikel.0.id', $artikel->id));
});

test('homepage excludes inactive and unpublished content', function (): void {
    Destinasi::factory()->create(['status' => false]);
    Pengalaman::factory()->create(['status' => false]);
    TempatWisata::factory()->create(['status' => false]);
    PaketPerjalanan::factory()->create(['status' => false]);
    Artikel::factory()->create(['status' => false, 'tanggal_publikasi' => now()->subDay()]);
    Artikel::factory()->create(['status' => true, 'tanggal_publikasi' => now()->addDay()]);

    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->has('destinasi', 0)->has('pengalaman', 0)->has('wisata', 0)->has('paket', 0)->has('artikel', 0));
});

test('homepage prioritizes featured records and configured order', function (): void {
    $regular = Destinasi::factory()->create(['status' => true, 'unggulan' => false, 'urutan' => 0]);
    $featuredLater = Destinasi::factory()->create(['status' => true, 'unggulan' => true, 'urutan' => 10]);
    $featuredFirst = Destinasi::factory()->create(['status' => true, 'unggulan' => true, 'urutan' => 1]);

    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page->where('destinasi.0.id', $featuredFirst->id)->where('destinasi.1.id', $featuredLater->id)->where('destinasi.2.id', $regular->id));
});
