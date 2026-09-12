<?php

use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\Pengalaman;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;

test('master seeding creates exactly the approved active reference data', function () {
    $this->seed(MasterDataSeeder::class);

    expect(Pengalaman::orderBy('slug')->pluck('nama', 'slug')->all())->toBe([
        'alam' => 'Alam',
        'budaya' => 'Budaya',
        'hidden-gem' => 'Hidden Gem',
        'keluarga' => 'Keluarga',
        'kuliner' => 'Kuliner',
        'pantai' => 'Pantai',
        'petualangan' => 'Petualangan',
    ]);
    expect(Aktivitas::orderBy('slug')->pluck('nama', 'slug')->all())->toBe([
        'camping' => 'Camping',
        'fotografi' => 'Fotografi',
        'hiking' => 'Hiking',
        'snorkeling' => 'Snorkeling',
    ]);
    expect(Fasilitas::orderBy('slug')->pluck('nama', 'slug')->all())->toBe([
        'area-parkir' => 'Area Parkir',
        'musala' => 'Musala',
        'tempat-makan' => 'Tempat Makan',
        'toilet' => 'Toilet',
    ]);
    expect(Pengalaman::where('status', false)->count())->toBe(0);
    expect(Aktivitas::where('status', false)->count())->toBe(0);
    expect(Fasilitas::where('status', false)->count())->toBe(0);
    $this->assertDatabaseCount('destinasi', 0);
    $this->assertDatabaseCount('users', 0);
});

test('repeated master seeding does not duplicate data or overwrite admin edits', function () {
    $this->seed(MasterDataSeeder::class);
    $pengalaman = Pengalaman::where('slug', 'alam')->firstOrFail();
    $pengalaman->update([
        'nama' => 'Alam Pilihan Admin',
        'deskripsi' => 'Deskripsi yang disunting admin.',
        'ikon_atau_gambar' => 'gambar:pengalaman/alam.webp',
        'status' => false,
    ]);
    $aktivitas = Aktivitas::where('slug', 'hiking')->firstOrFail();
    $aktivitas->update(['nama' => 'Hiking Pilihan Admin', 'status' => false]);
    $fasilitas = Fasilitas::where('slug', 'toilet')->firstOrFail();
    $fasilitas->update(['nama' => 'Toilet Pilihan Admin', 'status' => false]);
    $pengalamanBefore = Pengalaman::orderBy('id')->get()->toArray();
    $aktivitasBefore = Aktivitas::orderBy('id')->get()->toArray();
    $fasilitasBefore = Fasilitas::orderBy('id')->get()->toArray();
    $this->travel(1)->days();

    $this->seed(MasterDataSeeder::class);

    $this->assertDatabaseCount('pengalaman', 7);
    $this->assertDatabaseCount('aktivitas', 4);
    $this->assertDatabaseCount('fasilitas', 4);
    expect(Pengalaman::orderBy('id')->get()->toArray())->toBe($pengalamanBefore);
    expect(Aktivitas::orderBy('id')->get()->toArray())->toBe($aktivitasBefore);
    expect(Fasilitas::orderBy('id')->get()->toArray())->toBe($fasilitasBefore);
});

test('master seeding preserves existing admins destinations and custom references', function () {
    $admin = User::factory()->admin()->create();
    $destinasi = Destinasi::factory()->aktif()->create();
    $pengalaman = Pengalaman::factory()->aktif()->create(['slug' => 'pengalaman-khusus']);
    $aktivitas = Aktivitas::factory()->aktif()->create(['slug' => 'aktivitas-khusus']);
    $fasilitas = Fasilitas::factory()->aktif()->create(['slug' => 'fasilitas-khusus']);
    $adminBefore = $admin->getAttributes();
    $destinasiBefore = $destinasi->getAttributes();

    $this->seed(MasterDataSeeder::class);

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('users', $adminBefore);
    $this->assertDatabaseCount('destinasi', 1);
    $this->assertDatabaseHas('destinasi', $destinasiBefore);
    $this->assertDatabaseHas('pengalaman', $pengalaman->getAttributes());
    $this->assertDatabaseHas('aktivitas', $aktivitas->getAttributes());
    $this->assertDatabaseHas('fasilitas', $fasilitas->getAttributes());
    $this->assertDatabaseCount('pengalaman', 8);
    $this->assertDatabaseCount('aktivitas', 5);
    $this->assertDatabaseCount('fasilitas', 5);
});
