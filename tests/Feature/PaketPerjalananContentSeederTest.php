<?php

use App\Models\PaketPerjalanan;
use App\Models\User;
use Database\Seeders\DestinasiContentSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\PaketPerjalananContentSeeder;
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

it('seeds complete active packages with valid relations photos and ordered itinerary', function (): void {
    $this->seed(PaketPerjalananContentSeeder::class);

    expect(PaketPerjalanan::query()->count())->toBe(9)
        ->and(PaketPerjalanan::query()->where('status', true)->count())->toBe(9)
        ->and(PaketPerjalanan::query()->where('unggulan', true)->count())->toBe(6)
        ->and(PaketPerjalananContentSeeder::PHOTO_CREDITS)->toHaveCount(9);

    PaketPerjalanan::query()
        ->with(['destinasi', 'tempatWisata', 'itinerary.kegiatan'])
        ->get()
        ->each(function (PaketPerjalanan $package): void {
            expect($package->deskripsi)->not->toBeEmpty()
                ->and($package->highlight)->not->toBeEmpty()
                ->and($package->termasuk)->not->toBeEmpty()
                ->and($package->tidak_termasuk)->not->toBeEmpty()
                ->and((float) $package->harga_mulai)->toBeGreaterThan(0)
                ->and($package->destinasi)->not->toBeEmpty()
                ->and($package->tempatWisata)->not->toBeEmpty()
                ->and($package->itinerary)->toHaveCount($package->durasi_hari);

            Storage::disk('public')->assertExists($package->foto_utama);
            expect(Storage::disk('public')->size($package->foto_utama))->toBeLessThan(400_000)
                ->and(PaketPerjalananContentSeeder::PHOTO_CREDITS)->toHaveKey($package->slug);

            $package->itinerary->each(function ($day): void {
                expect($day->kegiatan)->not->toBeEmpty()
                    ->and($day->kegiatan->pluck('urutan')->all())->toBe($day->kegiatan->pluck('urutan')->sort()->values()->all());
            });
        });
});

it('is idempotent without duplicating relations days or activities', function (): void {
    $this->seed(PaketPerjalananContentSeeder::class);
    $before = [
        'packages' => PaketPerjalanan::query()->count(),
        'destinations' => DB::table('destinasi_paket_perjalanan')->count(),
        'attractions' => DB::table('paket_perjalanan_tempat_wisata')->count(),
        'days' => DB::table('itinerary')->count(),
        'activities' => DB::table('kegiatan_itinerary')->count(),
    ];

    $this->seed(PaketPerjalananContentSeeder::class);

    expect([
        'packages' => PaketPerjalanan::query()->count(),
        'destinations' => DB::table('destinasi_paket_perjalanan')->count(),
        'attractions' => DB::table('paket_perjalanan_tempat_wisata')->count(),
        'days' => DB::table('itinerary')->count(),
        'activities' => DB::table('kegiatan_itinerary')->count(),
    ])->toBe($before);
});

it('renders seeded packages and ordered itinerary on public and admin pages', function (): void {
    $this->seed(PaketPerjalananContentSeeder::class);
    $package = PaketPerjalanan::query()->where('slug', 'warisan-keraton-cirebon')->firstOrFail();

    $this->get(route('paket-perjalanan.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('paket-perjalanan/index')
            ->has('paketPerjalanan.data', 9));

    $this->get(route('paket-perjalanan.show', $package->slug))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('paket-perjalanan/show')
            ->where('paketPerjalanan.nama', 'Warisan Keraton Cirebon')
            ->where('paketPerjalanan.itinerary.0.hari_ke', 1)
            ->where('paketPerjalanan.itinerary.1.hari_ke', 2)
            ->where('paketPerjalanan.itinerary.0.kegiatan.0.judul', 'Penjemputan di Kota Cirebon'));

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.paket-perjalanan.edit', $package))
        ->assertOk();
});
