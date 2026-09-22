<?php

use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\KategoriArtikel;
use App\Models\TempatWisata;
use App\Models\User;
use Database\Seeders\KategoriArtikelSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('category seeder creates the six initial categories idempotently', function (): void {
    $this->seed(KategoriArtikelSeeder::class);
    $this->seed(KategoriArtikelSeeder::class);
    expect(KategoriArtikel::query()->pluck('nama')->all())->toEqualCanonicalizing(['Panduan Perjalanan', 'Itinerary', 'Budaya', 'Kuliner', 'Hidden Gem', 'Tips Perjalanan']);
});

test('admin creates article with destination and attraction relations', function (): void {
    $kategori = KategoriArtikel::factory()->create();
    $destinasi = Destinasi::factory()->create();
    $wisata = TempatWisata::factory()->for($destinasi, 'destinasi')->create();
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.artikel.store'), ['kategori_artikel_id' => $kategori->id, 'judul' => 'Panduan Bandung', 'ringkasan' => 'Ringkas', 'konten' => 'Konten artikel', 'status' => true, 'unggulan' => true, 'tanggal_publikasi' => now()->subHour()->format('Y-m-d\TH:i'), 'destinasi' => [$destinasi->id], 'tempat_wisata' => [$wisata->id]])->assertSessionHasNoErrors();
    $artikel = Artikel::query()->sole();
    expect($artikel->slug)->toBe('panduan-bandung');
    $this->assertDatabaseHas('artikel_destinasi', ['artikel_id' => $artikel->id, 'destinasi_id' => $destinasi->id]);
    $this->assertDatabaseHas('artikel_tempat_wisata', ['artikel_id' => $artikel->id, 'tempat_wisata_id' => $wisata->id]);
});

test('public inspiration only shows active published articles and featured content', function (): void {
    $published = Artikel::factory()->create(['status' => true, 'unggulan' => true, 'tanggal_publikasi' => now()->subDay()]);
    Artikel::factory()->create(['status' => false, 'tanggal_publikasi' => now()->subDay()]);
    Artikel::factory()->create(['status' => true, 'tanggal_publikasi' => now()->addDay()]);
    Artikel::factory()->create(['status' => true, 'tanggal_publikasi' => null]);
    $this->get(route('inspirasi.index'))->assertInertia(fn (Assert $page) => $page->component('inspirasi/index')->has('artikel.data', 1)->where('artikel.data.0.id', $published->id)->has('unggulan', 1)->where('unggulan.0.id', $published->id));
});

test('public detail includes category and related places while drafts are hidden', function (): void {
    $destinasi = Destinasi::factory()->create();
    $wisata = TempatWisata::factory()->for($destinasi, 'destinasi')->create();
    $artikel = Artikel::factory()->create(['status' => true, 'tanggal_publikasi' => now()->subHour()]);
    $artikel->destinasi()->attach($destinasi);
    $artikel->tempatWisata()->attach($wisata);
    $draft = Artikel::factory()->create(['status' => false]);
    $this->get(route('inspirasi.show', $artikel->slug))->assertInertia(fn (Assert $page) => $page->component('inspirasi/show')->where('artikel.kategori.id', $artikel->kategori_artikel_id)->where('artikel.destinasi.0.id', $destinasi->id)->where('artikel.tempat_wisata.0.id', $wisata->id));
    $this->get(route('inspirasi.show', $draft->slug))->assertNotFound();
});

test('article admin routes reject guests and ordinary users', function (): void {
    $this->get('/admin/artikel')->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get('/admin/artikel')->assertForbidden();
});
