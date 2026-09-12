<?php

use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\Pengalaman;
use Illuminate\Database\QueryException;

dataset('master models', [
    'destinasi' => [Destinasi::class, 'destinasi'],
    'pengalaman' => [Pengalaman::class, 'pengalaman'],
    'aktivitas' => [Aktivitas::class, 'aktivitas'],
    'fasilitas' => [Fasilitas::class, 'fasilitas'],
]);

test('master records can be saved with only a name and slug and default to inactive', function (string $modelClass, string $table) {
    $master = $modelClass::factory()->create([
        'nama' => 'Master Baru',
        'slug' => 'master-baru',
    ])->refresh();

    $this->assertDatabaseHas($table, [
        'id' => $master->id,
        'nama' => 'Master Baru',
        'slug' => 'master-baru',
        'status' => false,
    ]);
    expect($master->status)->toBeFalse();
    expect($master->created_at)->not->toBeNull();
    expect($master->updated_at)->not->toBeNull();
})->with('master models');

test('active factory states persist boolean status', function (string $modelClass, string $table) {
    $master = $modelClass::factory()->aktif()->create()->refresh();

    expect($master->status)->toBeTrue();
    $this->assertDatabaseHas($table, ['id' => $master->id, 'status' => true]);
})->with('master models');

test('duplicate slugs are rejected within each master table', function (string $modelClass, string $table) {
    $modelClass::factory()->create(['slug' => 'slug-sama']);

    expect(fn () => $modelClass::factory()->create(['slug' => 'slug-sama']))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount($table, 1);
})->with('master models');

test('names may be shared by records with different slugs', function (string $modelClass, string $table) {
    $modelClass::factory()->create(['nama' => 'Nama Sama', 'slug' => 'nama-sama']);
    $modelClass::factory()->create(['nama' => 'Nama Sama', 'slug' => 'nama-sama-lain']);

    $this->assertDatabaseCount($table, 2);
})->with('master models');

test('different master tables can use the same slug', function () {
    Destinasi::factory()->create(['slug' => 'alam']);
    Pengalaman::factory()->create(['slug' => 'alam']);
    Aktivitas::factory()->create(['slug' => 'alam']);
    Fasilitas::factory()->create(['slug' => 'alam']);

    $this->assertDatabaseHas('destinasi', ['slug' => 'alam']);
    $this->assertDatabaseHas('pengalaman', ['slug' => 'alam']);
    $this->assertDatabaseHas('aktivitas', ['slug' => 'alam']);
    $this->assertDatabaseHas('fasilitas', ['slug' => 'alam']);
});

test('destinations allow unfinished content and default to unfeatured with zero order', function () {
    $destinasi = Destinasi::factory()->create()->refresh();

    $this->assertDatabaseHas('destinasi', [
        'id' => $destinasi->id,
        'deskripsi_singkat' => null,
        'deskripsi_lengkap' => null,
        'foto_utama' => null,
        'lokasi' => null,
        'waktu_terbaik' => null,
        'cara_menuju' => null,
        'transportasi_lokal' => null,
        'tips' => null,
    ]);
    expect($destinasi->unggulan)->toBeFalse();
    expect($destinasi->urutan)->toBe(0);
});

test('complete destination content and display settings can be persisted', function () {
    $attributes = [
        'deskripsi_singkat' => 'Destinasi pegunungan Jawa Barat.',
        'deskripsi_lengkap' => 'Deskripsi lengkap destinasi.',
        'foto_utama' => 'destinasi/pegunungan.webp',
        'lokasi' => 'Jawa Barat',
        'waktu_terbaik' => 'Pagi hari',
        'cara_menuju' => 'Melalui jalan utama.',
        'transportasi_lokal' => 'Angkutan lokal.',
        'tips' => 'Bawa perlengkapan yang sesuai.',
        'urutan' => 10,
    ];

    $destinasi = Destinasi::factory()->aktif()->unggulan()->create($attributes)->refresh();

    $this->assertDatabaseHas('destinasi', ['id' => $destinasi->id, ...$attributes]);
    expect($destinasi->unggulan)->toBeTrue();
    expect($destinasi->status)->toBeTrue();
    expect($destinasi->urutan)->toBe(10);
});

test('experience descriptions and media references are optional', function () {
    $pengalaman = Pengalaman::factory()->create()->refresh();

    expect($pengalaman->deskripsi)->toBeNull();
    expect($pengalaman->ikon_atau_gambar)->toBeNull();
});

test('experience descriptions and media references can be persisted', function () {
    $pengalaman = Pengalaman::factory()->create([
        'deskripsi' => 'Menjelajahi alam Jawa Barat.',
        'ikon_atau_gambar' => 'ikon:mountain',
    ]);

    $this->assertDatabaseHas('pengalaman', [
        'id' => $pengalaman->id,
        'deskripsi' => 'Menjelajahi alam Jawa Barat.',
        'ikon_atau_gambar' => 'ikon:mountain',
    ]);
});
