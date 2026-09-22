<?php

use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
    $this->admin = User::factory()->admin()->create();
});

dataset('admin master data', [
    'pengalaman' => [Pengalaman::class, 'pengalaman', 'pengalaman'],
    'aktivitas' => [Aktivitas::class, 'aktivitas', 'aktivitas'],
    'fasilitas' => [Fasilitas::class, 'fasilitas', 'fasilitas'],
]);

test('master data pages require authenticated admin access', function (string $path): void {
    $this->get("/admin/{$path}")->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->get("/admin/{$path}")->assertForbidden();
})->with(['pengalaman', 'aktivitas', 'fasilitas']);

test('admin can search and paginate master data', function (string $model, string $path): void {
    $model::factory()->count(16)->create();
    $match = $model::factory()->create(['nama' => 'Pilihan Khusus', 'slug' => 'pilihan-khusus']);

    $this->actingAs($this->admin)->get("/admin/{$path}")->assertInertia(fn (Assert $page) => $page
        ->component("admin/{$path}/index")->has('items.data', 15)->where('items.total', 17));
    $this->actingAs($this->admin)->get("/admin/{$path}?search=Pilihan%20Khusus")->assertInertia(fn (Assert $page) => $page
        ->has('items.data', 1)->where('items.data.0.id', $match->id)->where('filters.search', 'Pilihan Khusus'));
})->with([
    [Pengalaman::class, 'pengalaman'],
    [Aktivitas::class, 'aktivitas'],
    [Fasilitas::class, 'fasilitas'],
]);

test('admin can create update status and delete unused master data', function (string $model, string $path, string $table): void {
    $create = ['nama' => 'Wisata Keluarga', 'status' => true];
    if ($model === Pengalaman::class) {
        $create += ['deskripsi' => 'Cocok untuk keluarga.', 'ikon_atau_gambar' => 'ikon:keluarga'];
    }

    $this->actingAs($this->admin)->post("/admin/{$path}", $create)->assertRedirect();
    $item = $model::query()->where('slug', 'wisata-keluarga')->firstOrFail();
    $this->assertDatabaseHas($table, ['id' => $item->id, 'nama' => 'Wisata Keluarga', 'status' => true]);

    $update = ['nama' => 'Keluarga Ceria', 'status' => false];
    if ($model === Pengalaman::class) {
        $update += ['deskripsi' => null, 'ikon_atau_gambar' => null];
    }
    $this->actingAs($this->admin)->put("/admin/{$path}/{$item->id}", $update)->assertRedirect();
    $this->assertDatabaseHas($table, ['id' => $item->id, 'nama' => 'Keluarga Ceria', 'slug' => 'keluarga-ceria', 'status' => false]);

    $this->actingAs($this->admin)->delete("/admin/{$path}/{$item->id}")->assertRedirect();
    $this->assertDatabaseMissing($table, ['id' => $item->id]);
})->with('admin master data');

test('master validation rejects invalid and manipulated payloads', function (string $path): void {
    $this->actingAs($this->admin)->post("/admin/{$path}", ['nama' => '', 'status' => 'aktif', 'slug' => 'slug-paksaan'])
        ->assertSessionHasErrors(['nama', 'status', 'slug']);
})->with(['pengalaman', 'aktivitas', 'fasilitas']);

test('generated slugs remain unique', function (string $model, string $path): void {
    $model::factory()->create(['nama' => 'Alam', 'slug' => 'alam']);
    $payload = ['nama' => 'Alam', 'status' => true];
    if ($model === Pengalaman::class) {
        $payload += ['deskripsi' => null, 'ikon_atau_gambar' => null];
    }

    $this->actingAs($this->admin)->post("/admin/{$path}", $payload)->assertRedirect();
    expect($model::query()->where('slug', 'alam-2')->exists())->toBeTrue();
})->with([
    [Pengalaman::class, 'pengalaman'],
    [Aktivitas::class, 'aktivitas'],
    [Fasilitas::class, 'fasilitas'],
]);

test('master data in use by a tourist attraction cannot be deleted', function (string $model, string $path, string $relation): void {
    $master = $model::factory()->create();
    $wisata = TempatWisata::factory()->for(Destinasi::factory(), 'destinasi')->create();
    $wisata->{$relation}()->attach($master);

    $this->actingAs($this->admin)->delete("/admin/{$path}/{$master->id}")
        ->assertRedirect()->assertSessionHasErrors($path);

    expect($master->fresh())->not->toBeNull();
    expect($wisata->{$relation}()->whereKey($master->id)->exists())->toBeTrue();
})->with('admin master data');
