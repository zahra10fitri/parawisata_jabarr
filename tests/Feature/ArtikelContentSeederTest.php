<?php

use App\Models\Artikel;
use App\Models\KategoriArtikel;
use App\Models\User;
use Database\Seeders\ArtikelContentSeeder;
use Database\Seeders\DestinasiContentSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\TempatWisataContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    Storage::fake('public');
    $this->seed(MasterDataSeeder::class);
    $this->seed(DestinasiContentSeeder::class);
    $this->seed(TempatWisataContentSeeder::class);
});

it('seeds published original articles across all existing categories', function (): void {
    $this->seed(ArtikelContentSeeder::class);

    expect(Artikel::query()->count())->toBe(12)
        ->and(Artikel::query()->where('status', true)->count())->toBe(12)
        ->and(Artikel::query()->where('unggulan', true)->count())->toBe(5)
        ->and(ArtikelContentSeeder::PHOTO_CREDITS)->toHaveCount(12);

    KategoriArtikel::query()->each(function (KategoriArtikel $category): void {
        expect($category->artikel()->count())->toBe(2);
    });

    Artikel::query()
        ->with(['kategori', 'destinasi', 'tempatWisata'])
        ->get()
        ->each(function (Artikel $article): void {
            expect($article->ringkasan)->not->toBeEmpty()
                ->and(mb_strlen($article->konten))->toBeGreaterThan(700)
                ->and($article->tanggal_publikasi)->not->toBeNull()
                ->and($article->tanggal_publikasi->isPast())->toBeTrue()
                ->and($article->kategori)->not->toBeNull()
                ->and($article->destinasi)->not->toBeEmpty();

            Storage::disk('public')->assertExists($article->foto_utama);
            expect(Storage::disk('public')->size($article->foto_utama))->toBeLessThan(400_000)
                ->and(ArtikelContentSeeder::PHOTO_CREDITS)->toHaveKey($article->slug);
        });
});

it('is idempotent without duplicating articles or pivot relations', function (): void {
    $this->seed(ArtikelContentSeeder::class);
    $before = [
        'articles' => Artikel::query()->count(),
        'destinations' => DB::table('artikel_destinasi')->count(),
        'attractions' => DB::table('artikel_tempat_wisata')->count(),
    ];

    $this->seed(ArtikelContentSeeder::class);

    expect([
        'articles' => Artikel::query()->count(),
        'destinations' => DB::table('artikel_destinasi')->count(),
        'attractions' => DB::table('artikel_tempat_wisata')->count(),
    ])->toBe($before);
});

it('renders seeded articles on admin public listing and public detail pages', function (): void {
    $this->seed(ArtikelContentSeeder::class);
    $article = Artikel::query()->where('slug', 'membaca-bandung-dari-bumi-ke-braga')->firstOrFail();

    $this->get(route('inspirasi.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('inspirasi/index')
            ->has('artikel.data', 12)
            ->has('unggulan', 3));

    $this->get(route('inspirasi.show', $article->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('inspirasi/show')
            ->where('artikel.judul', 'Membaca Bandung dari Bumi hingga Braga')
            ->where('artikel.kategori.nama', 'Panduan Perjalanan')
            ->has('artikel.destinasi', 1)
            ->has('artikel.tempat_wisata', 2));

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.artikel.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/artikel/index')
            ->has('artikel.data', 12));
});
