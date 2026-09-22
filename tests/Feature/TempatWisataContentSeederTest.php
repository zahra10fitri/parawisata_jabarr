<?php

use App\Models\Destinasi;
use App\Models\TempatWisata;
use Database\Seeders\DestinasiContentSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\TempatWisataContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
    $this->seed(MasterDataSeeder::class);
    $this->seed(DestinasiContentSeeder::class);
});

it('seeds two active tourist attractions for every stage 20A city with local photos and relations', function (): void {
    $this->seed(TempatWisataContentSeeder::class);

    expect(TempatWisata::query()->count())->toBe(16)
        ->and(TempatWisata::query()->where('status', true)->count())->toBe(16)
        ->and(TempatWisataContentSeeder::PHOTO_CREDITS)->toHaveCount(16);

    Destinasi::query()->orderBy('id')->each(function (Destinasi $destination): void {
        expect($destination->tempatWisata()->count())->toBe(2);
    });

    TempatWisata::query()
        ->with(['pengalaman', 'aktivitas', 'fasilitas'])
        ->get()
        ->each(function (TempatWisata $place): void {
            expect($place->deskripsi_singkat)->not->toBeEmpty()
                ->and($place->deskripsi_lengkap)->not->toBeEmpty()
                ->and($place->lokasi)->not->toBeEmpty()
                ->and($place->waktu_terbaik)->not->toBeEmpty()
                ->and($place->tips)->not->toBeEmpty()
                ->and($place->jam_operasional)->toBeNull()
                ->and($place->harga_tiket)->toBeNull()
                ->and($place->pengalaman)->not->toBeEmpty()
                ->and($place->aktivitas)->not->toBeEmpty()
                ->and($place->fasilitas)->not->toBeEmpty();

            Storage::disk('public')->assertExists($place->foto_utama);
            expect(Storage::disk('public')->size($place->foto_utama))->toBeLessThan(400_000)
                ->and(TempatWisataContentSeeder::PHOTO_CREDITS)->toHaveKey($place->slug);
        });
});

it('is idempotent and does not remove unrelated tourist attractions', function (): void {
    $unrelatedDestination = Destinasi::factory()->create();
    $unrelatedPlace = TempatWisata::factory()->for($unrelatedDestination, 'destinasi')->create();

    $this->seed(TempatWisataContentSeeder::class);
    $this->seed(TempatWisataContentSeeder::class);

    expect(TempatWisata::query()->count())->toBe(17)
        ->and(TempatWisata::query()->whereIn('slug', array_keys(TempatWisataContentSeeder::PHOTO_CREDITS))->count())->toBe(16)
        ->and($unrelatedPlace->fresh())->not->toBeNull();
});
