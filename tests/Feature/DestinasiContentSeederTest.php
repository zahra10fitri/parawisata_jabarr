<?php

use App\Models\Destinasi;
use App\Models\User;
use Database\Seeders\DestinasiContentSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
    Storage::fake('public');
});

test('destination content seeder creates eight complete active destinations with local photos', function (): void {
    $this->seed(DestinasiContentSeeder::class);

    $expected = ['Kota Bandung', 'Kota Banjar', 'Kota Bekasi', 'Kota Bogor', 'Kota Cimahi', 'Kota Cirebon', 'Kota Depok', 'Kota Sukabumi'];
    $destinations = Destinasi::query()->orderBy('urutan')->get();

    expect($destinations)->toHaveCount(8)
        ->and($destinations->pluck('nama')->all())->toBe($expected)
        ->and($destinations->every(fn (Destinasi $item): bool => $item->status))->toBeTrue()
        ->and($destinations->where('unggulan', true))->toHaveCount(4)
        ->and(array_keys(DestinasiContentSeeder::PHOTO_CREDITS))->toEqualCanonicalizing($destinations->pluck('slug')->all());

    foreach ($destinations as $destination) {
        expect($destination->deskripsi_singkat)->not->toBeEmpty()
            ->and($destination->deskripsi_lengkap)->not->toBeEmpty()
            ->and($destination->lokasi)->not->toBeEmpty()
            ->and($destination->waktu_terbaik)->not->toBeEmpty()
            ->and($destination->cara_menuju)->not->toBeEmpty()
            ->and($destination->transportasi_lokal)->not->toBeEmpty()
            ->and($destination->tips)->not->toBeEmpty()
            ->and($destination->foto_utama)->toBe("destinasi/{$destination->slug}.webp");
        Storage::disk('public')->assertExists($destination->foto_utama);
        expect(Storage::disk('public')->mimeType($destination->foto_utama))->toBe('image/webp')
            ->and(Storage::disk('public')->size($destination->foto_utama))->toBeLessThan(400 * 1024);
    }
});

test('destination content seeder is idempotent and preserves unrelated destinations', function (): void {
    $unrelated = Destinasi::factory()->create(['nama' => 'Destinasi Milik Admin', 'slug' => 'destinasi-milik-admin']);

    $this->seed(DestinasiContentSeeder::class);
    $this->seed(DestinasiContentSeeder::class);

    expect(Destinasi::query()->whereIn('slug', array_keys(DestinasiContentSeeder::PHOTO_CREDITS))->count())->toBe(8)
        ->and($unrelated->fresh()?->nama)->toBe('Destinasi Milik Admin');
    $this->assertDatabaseCount('destinasi', 9);
});

test('seeded destinations are visible on the admin list', function (): void {
    $this->seed(DestinasiContentSeeder::class);

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.destinasi.index'))->assertInertia(fn (Assert $page) => $page
        ->component('admin/destinasi/index')
        ->where('destinasi.total', 8)
        ->has('destinasi.data', 8));
});
