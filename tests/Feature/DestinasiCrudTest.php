<?php

use App\Models\Destinasi;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

/** @return array{nama: string, status: bool, unggulan: bool, urutan: int} */
function destinasiInput(): array
{
    return ['nama' => 'Pantai Pangandaran', 'status' => true, 'unggulan' => false, 'urutan' => 2];
}

test('admins can create destinasi with all content fields and view its detail', function () {
    $this->actingAs(User::factory()->admin()->create());
    $data = [
        ...destinasiInput(),
        'deskripsi_singkat' => 'Pantai di Jawa Barat.',
        'deskripsi_lengkap' => 'Deskripsi lengkap pantai.',
        'lokasi' => 'Pangandaran',
        'waktu_terbaik' => 'Pagi',
        'cara_menuju' => 'Jalan raya',
        'transportasi_lokal' => 'Becak',
        'tips' => 'Bawa topi.',
    ];

    $response = $this->post(route('admin.destinasi.store'), $data);

    $destinasi = Destinasi::query()->sole();
    $response->assertSessionHasNoErrors()->assertRedirect(route('admin.destinasi.show', $destinasi));
    $this->assertDatabaseHas('destinasi', [...$data, 'slug' => 'pantai-pangandaran', 'foto_utama' => null]);
    $this->get(route('admin.destinasi.show', $destinasi))->assertInertia(fn (Assert $page) => $page
        ->component('admin/destinasi/show')
        ->where('destinasi.nama', 'Pantai Pangandaran')
        ->where('destinasi.tips', 'Bawa topi.')
        ->where('destinasi.status', true)
        ->where('destinasi.foto_utama_url', null));
});

test('admins can open create and edit pages', function () {
    $destinasi = Destinasi::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.destinasi.create'))->assertInertia(fn (Assert $page) => $page->component('admin/destinasi/create'));
    $this->get(route('admin.destinasi.edit', $destinasi))->assertInertia(fn (Assert $page) => $page
        ->component('admin/destinasi/edit')->where('destinasi.id', $destinasi->id));
});

test('admins can update content flags and ordering without changing an unchanged names slug', function () {
    $destinasi = Destinasi::factory()->aktif()->unggulan()->create(['nama' => 'Pantai', 'slug' => 'pantai-2']);
    $this->actingAs(User::factory()->admin()->create());

    $this->put(route('admin.destinasi.update', $destinasi), [
        'nama' => 'Pantai', 'status' => false, 'unggulan' => false, 'urutan' => 0, 'tips' => 'Tips baru',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.destinasi.show', $destinasi));

    $this->assertDatabaseHas('destinasi', [
        'id' => $destinasi->id, 'slug' => 'pantai-2', 'status' => false, 'unggulan' => false, 'urutan' => 0, 'tips' => 'Tips baru',
    ]);
});

test('renaming a destinasi regenerates its slug and avoids existing slugs', function () {
    Destinasi::factory()->create(['nama' => 'Pantai', 'slug' => 'pantai']);
    $destinasi = Destinasi::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.destinasi.update', $destinasi), [
        ...destinasiInput(), 'nama' => 'Pantai',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('destinasi', ['id' => $destinasi->id, 'slug' => 'pantai-2']);
});

test('duplicate names receive unique numbered slugs', function () {
    Destinasi::factory()->create(['slug' => 'pantai-pangandaran']);
    Destinasi::factory()->create(['slug' => 'pantai-pangandaran-2']);

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.store'), destinasiInput())->assertSessionHasNoErrors();

    $this->assertDatabaseHas('destinasi', ['nama' => 'Pantai Pangandaran', 'slug' => 'pantai-pangandaran-3']);
});

test('slug generation handles empty transliteration and maximum length duplicates', function (string $name, string $existing, string $expected) {
    Destinasi::factory()->create(['slug' => $existing]);

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.store'), [
        ...destinasiInput(), 'nama' => $name,
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('destinasi', ['nama' => $name, 'slug' => $expected]);
})->with([
    'symbols' => ['!!!', 'destinasi', 'destinasi-2'],
    'maximum length' => [str_repeat('a', 255), str_repeat('a', 255), str_repeat('a', 253).'-2'],
]);

test('a slug collision during insert is retried', function () {
    $this->actingAs(User::factory()->admin()->create());
    $collision = true;
    Destinasi::creating(function (Destinasi $record) use (&$collision): void {
        if ($collision) {
            $collision = false;
            DB::table('destinasi')->insert(['nama' => 'Concurrent', 'slug' => $record->slug]);
        }
    });

    $this->post(route('admin.destinasi.store'), destinasiInput())->assertSessionHasNoErrors();

    $this->assertDatabaseHas('destinasi', ['nama' => 'Pantai Pangandaran']);
    $this->assertDatabaseCount('destinasi', 1);
});

test('admins can delete a destinasi without a photo', function () {
    $destinasi = Destinasi::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $destinasi))
        ->assertRedirect(route('admin.destinasi.index'));

    $this->assertModelMissing($destinasi);
});

test('destinasi list searches only names and paginates fifteen in stable order', function () {
    $matches = Destinasi::factory()->count(16)->sequence(fn ($sequence) => [
        'nama' => 'Pantai '.$sequence->index, 'urutan' => 1,
    ])->create();
    Destinasi::factory()->create(['nama' => 'Gunung', 'lokasi' => 'Pantai', 'urutan' => 0]);
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.destinasi.index', ['search' => 'Pantai']))->assertInertia(fn (Assert $page) => $page
        ->component('admin/destinasi/index')->has('destinasi.data', 15)
        ->where('destinasi.total', 16)->where('destinasi.data.0.id', $matches->first()->id)
        ->where('destinasi.data.14.id', $matches[14]->id)
        ->where('filters.search', 'Pantai')
        ->where('destinasi.next_page_url', fn (string $url): bool => str_contains($url, 'search=Pantai') && str_contains($url, 'page=2')));
    $this->get(route('admin.destinasi.index', ['search' => 'Pantai', 'page' => 2]))->assertInertia(fn (Assert $page) => $page
        ->has('destinasi.data', 1)->where('destinasi.data.0.id', $matches->last()->id));
});

test('search treats wildcards and SQL fragments as literal text', function (string $search) {
    $match = Destinasi::factory()->create(['nama' => 'Destinasi '.$search]);
    Destinasi::factory()->create(['nama' => 'Destinasi biasa']);

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.destinasi.index', ['search' => $search]))
        ->assertInertia(fn (Assert $page) => $page->has('destinasi.data', 1)->where('destinasi.data.0.id', $match->id));
})->with(['%', '_', '!', "' OR 1=1 --"]);

test('destinasi list orders by urutan before id and supports an empty result', function () {
    Destinasi::factory()->create(['urutan' => 5]);
    $first = Destinasi::factory()->create(['urutan' => 0]);
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.destinasi.index'))->assertInertia(fn (Assert $page) => $page->where('destinasi.data.0.id', $first->id));
    $this->get(route('admin.destinasi.index', ['search' => 'no-result-xyz']))->assertInertia(fn (Assert $page) => $page->has('destinasi.data', 0));
});

test('required destinasi fields are validated before insertion', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.store'), [])
        ->assertSessionHasErrors(['nama' => 'nama wajib diisi.', 'status', 'unggulan', 'urutan']);

    $this->assertDatabaseCount('destinasi', 0);
});

test('invalid destinasi input cannot create or update records', function (string $field, mixed $value) {
    $destinasi = Destinasi::factory()->create();
    $original = $destinasi->getAttributes();
    $this->actingAs(User::factory()->admin()->create());
    $data = [...destinasiInput(), $field => $value];

    $this->post(route('admin.destinasi.store'), $data)->assertSessionHasErrors($field);
    $this->put(route('admin.destinasi.update', $destinasi), $data)->assertSessionHasErrors($field);

    $this->assertDatabaseCount('destinasi', 1);
    $this->assertDatabaseHas('destinasi', $original);
})->with([
    'blank name' => ['nama', '   '],
    'name type' => ['nama', ['invalid']],
    'name length' => ['nama', str_repeat('a', 256)],
    'manual slug' => ['slug', 'manual'],
    'short description' => ['deskripsi_singkat', str_repeat('a', 1001)],
    'long description' => ['deskripsi_lengkap', str_repeat('a', 50001)],
    'location' => ['lokasi', str_repeat('a', 256)],
    'best time' => ['waktu_terbaik', str_repeat('a', 5001)],
    'directions' => ['cara_menuju', str_repeat('a', 5001)],
    'transport' => ['transportasi_lokal', str_repeat('a', 5001)],
    'tips' => ['tips', str_repeat('a', 5001)],
    'status' => ['status', 'active'],
    'featured' => ['unggulan', 'yes'],
    'negative order' => ['urutan', -1],
    'fraction order' => ['urutan', 1.5],
    'overflow order' => ['urutan', 4294967296],
]);

test('optional fields normalize to null and unknown attributes are not assigned', function () {
    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.store'), [
        ...destinasiInput(), 'nama' => '  Pantai  ', 'tips' => '   ', 'lokasi' => '', 'id' => 98765, 'urutan' => 4294967295,
    ])->assertSessionHasNoErrors();

    $record = Destinasi::query()->sole();
    expect($record->id)->not->toBe(98765);
    $this->assertDatabaseHas('destinasi', ['nama' => 'Pantai', 'tips' => null, 'lokasi' => null, 'urutan' => 4294967295]);
});

test('missing destinasi returns not found', function (string $method, string $action) {
    $this->actingAs(User::factory()->admin()->create())->call($method, route('admin.destinasi.'.$action, 999), destinasiInput())->assertNotFound();
})->with([['GET', 'show'], ['GET', 'edit'], ['PUT', 'update'], ['DELETE', 'destroy']]);

test('invalid search parameters are rejected', function (array $query, string $field) {
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.destinasi.index', $query))->assertSessionHasErrors($field);
})->with([
    'search too long' => [['search' => str_repeat('a', 256)], 'search'],
    'page zero' => [['page' => 0], 'page'],
    'page text' => [['page' => 'invalid'], 'page'],
]);

test('repeated slug collisions return a validation error without inserting a destinasi', function () {
    $this->actingAs(User::factory()->admin()->create());
    Destinasi::creating(function (Destinasi $record): void {
        DB::table('destinasi')->insert(['nama' => 'Concurrent', 'slug' => $record->slug]);
    });

    $this->post(route('admin.destinasi.store'), destinasiInput())
        ->assertSessionHasErrors(['nama' => 'Slug sedang digunakan oleh proses lain. Silakan simpan kembali.']);

    $this->assertDatabaseCount('destinasi', 0);
});

test('unrelated unique constraint errors are not disguised as slug validation errors', function () {
    $existing = Destinasi::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    Exceptions::fake();
    Destinasi::creating(function (Destinasi $record) use ($existing): void {
        $record->id = $existing->id;
    });

    $this->post(route('admin.destinasi.store'), destinasiInput())->assertServerError();

    $this->assertDatabaseCount('destinasi', 1);
    $this->assertModelExists($existing);
    Exceptions::assertReported(UniqueConstraintViolationException::class);
});
