<?php

use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

/** @return array{destinasi_id: int, nama: string, status: bool, unggulan: bool, urutan: int} */
function wisataInput(int $destinasiId): array
{
    return ['destinasi_id' => $destinasiId, 'nama' => 'Pantai Batu Hiu', 'status' => true, 'unggulan' => false, 'urutan' => 2];
}

test('admins create tempat wisata with all content and master relations', function () {
    $destinasi = Destinasi::factory()->create();
    $pengalaman = Pengalaman::factory()->create();
    $aktivitas = Aktivitas::factory()->create();
    $fasilitas = Fasilitas::factory()->create();
    $this->actingAs(User::factory()->admin()->create());
    $data = [
        ...wisataInput($destinasi->id), 'deskripsi_singkat' => 'Pantai indah', 'deskripsi_lengkap' => 'Deskripsi lengkap',
        'lokasi' => 'Pangandaran', 'jam_operasional' => '08:00–17:00', 'harga_tiket' => '15000.50', 'waktu_terbaik' => 'Pagi', 'tips' => 'Bawa topi',
        'pengalaman' => [$pengalaman->id], 'aktivitas' => [$aktivitas->id], 'fasilitas' => [$fasilitas->id],
    ];

    $response = $this->post(route('admin.tempat-wisata.store'), $data);

    $record = TempatWisata::query()->sole();
    $response->assertSessionHasNoErrors()->assertRedirect(route('admin.tempat-wisata.show', $record))->assertInertiaFlash('toast.type', 'success');
    $this->assertDatabaseHas('tempat_wisata', [
        'id' => $record->id, 'destinasi_id' => $destinasi->id, 'nama' => 'Pantai Batu Hiu', 'slug' => 'pantai-batu-hiu',
        'deskripsi_singkat' => 'Pantai indah', 'deskripsi_lengkap' => 'Deskripsi lengkap', 'lokasi' => 'Pangandaran',
        'jam_operasional' => '08:00–17:00', 'harga_tiket' => '15000.50', 'waktu_terbaik' => 'Pagi', 'tips' => 'Bawa topi', 'status' => true, 'unggulan' => false, 'urutan' => 2,
    ]);
    $this->assertDatabaseHas('pengalaman_tempat_wisata', ['tempat_wisata_id' => $record->id, 'pengalaman_id' => $pengalaman->id]);
    $this->assertDatabaseHas('aktivitas_tempat_wisata', ['tempat_wisata_id' => $record->id, 'aktivitas_id' => $aktivitas->id]);
    $this->assertDatabaseHas('fasilitas_tempat_wisata', ['tempat_wisata_id' => $record->id, 'fasilitas_id' => $fasilitas->id]);
    $this->get(route('admin.tempat-wisata.show', $record))->assertInertia(fn (Assert $page) => $page
        ->component('admin/tempat-wisata/show')->where('tempatWisata.destinasi.id', $destinasi->id)
        ->where('tempatWisata.harga_tiket', '15000.50')->where('tempatWisata.pengalaman.0.id', $pengalaman->id)
        ->where('tempatWisata.aktivitas.0.id', $aktivitas->id)->where('tempatWisata.fasilitas.0.id', $fasilitas->id));
});

test('create and edit include existing master options including inactive selections', function () {
    $record = TempatWisata::factory()->create();
    $pengalaman = Pengalaman::factory()->create();
    $record->pengalaman()->attach($pengalaman);
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.tempat-wisata.create'))->assertInertia(fn (Assert $page) => $page->component('admin/tempat-wisata/create')
        ->where('options.destinasi.0.id', $record->destinasi_id)->where('options.pengalaman.0.status', false));
    $this->get(route('admin.tempat-wisata.edit', $record))->assertInertia(fn (Assert $page) => $page->component('admin/tempat-wisata/edit')
        ->where('tempatWisata.pengalaman.0.id', $pengalaman->id)->has('tempatWisata.galeri', 0));
});

test('updates move destinations synchronize all masters and change display settings', function () {
    $record = TempatWisata::factory()->aktif()->unggulan()->create(['nama' => 'Pantai', 'slug' => 'pantai-2']);
    $oldPengalaman = Pengalaman::factory()->create();
    $newPengalaman = Pengalaman::factory()->create();
    $oldAktivitas = Aktivitas::factory()->create();
    $newAktivitas = Aktivitas::factory()->create();
    $oldFasilitas = Fasilitas::factory()->create();
    $newFasilitas = Fasilitas::factory()->create();
    $record->pengalaman()->attach($oldPengalaman);
    $record->aktivitas()->attach($oldAktivitas);
    $record->fasilitas()->attach($oldFasilitas);
    $destinasi = Destinasi::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->put(route('admin.tempat-wisata.update', $record), [
        ...wisataInput($destinasi->id), 'nama' => 'Pantai', 'status' => false, 'unggulan' => false, 'urutan' => 0,
        'pengalaman' => [$newPengalaman->id], 'aktivitas' => [$newAktivitas->id], 'fasilitas' => [$newFasilitas->id], 'harga_tiket' => 0,
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tempat_wisata', ['id' => $record->id, 'destinasi_id' => $destinasi->id, 'slug' => 'pantai-2', 'status' => false, 'unggulan' => false, 'urutan' => 0, 'harga_tiket' => 0]);
    expect($record->pengalaman()->pluck('pengalaman.id')->all())->toBe([$newPengalaman->id]);
    expect($record->aktivitas()->pluck('aktivitas.id')->all())->toBe([$newAktivitas->id]);
    expect($record->fasilitas()->pluck('fasilitas.id')->all())->toBe([$newFasilitas->id]);
});

test('empty selections detach all master relations', function () {
    $record = TempatWisata::factory()->create();
    $record->pengalaman()->attach(Pengalaman::factory()->create());
    $record->aktivitas()->attach(Aktivitas::factory()->create());
    $record->fasilitas()->attach(Fasilitas::factory()->create());

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.tempat-wisata.update', $record), [
        ...wisataInput($record->destinasi_id), 'pengalaman' => [], 'aktivitas' => [], 'fasilitas' => [],
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('pengalaman_tempat_wisata', 0);
    $this->assertDatabaseCount('aktivitas_tempat_wisata', 0);
    $this->assertDatabaseCount('fasilitas_tempat_wisata', 0);
});

test('slug collisions use suffixes and renamed records regenerate their slug', function () {
    $record = TempatWisata::factory()->create();
    TempatWisata::factory()->create(['slug' => 'pantai-batu-hiu']);
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.tempat-wisata.store'), wisataInput($record->destinasi_id))->assertSessionHasNoErrors();
    $this->put(route('admin.tempat-wisata.update', $record), wisataInput($record->destinasi_id))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tempat_wisata', ['slug' => 'pantai-batu-hiu-2']);
    $this->assertDatabaseHas('tempat_wisata', ['id' => $record->id, 'slug' => 'pantai-batu-hiu-3']);
});

test('slug handles symbols and maximum length collisions', function (string $name, string $existing, string $expected) {
    $record = TempatWisata::factory()->create(['slug' => $existing]);

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), [...wisataInput($record->destinasi_id), 'nama' => $name])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tempat_wisata', ['nama' => $name, 'slug' => $expected]);
})->with([
    'symbols' => ['!!!', 'tempat-wisata', 'tempat-wisata-2'],
    'maximum length' => [str_repeat('a', 255), str_repeat('a', 255), str_repeat('a', 253).'-2'],
]);

test('a slug collision during insertion is retried', function () {
    $destinasi = Destinasi::factory()->create();
    $collision = true;
    TempatWisata::creating(function (TempatWisata $record) use (&$collision): void {
        if ($collision) {
            $collision = false;
            DB::table('tempat_wisata')->insert(['destinasi_id' => $record->destinasi_id, 'nama' => 'Concurrent', 'slug' => $record->slug]);
        }
    });

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), wisataInput($destinasi->id))->assertSessionHasNoErrors();

    $this->assertDatabaseCount('tempat_wisata', 1);
    $this->assertDatabaseHas('tempat_wisata', ['nama' => 'Pantai Batu Hiu']);
});

test('search and destination filter combine and persist across pagination', function () {
    $destinasi = Destinasi::factory()->create();
    $matches = TempatWisata::factory()->count(16)->for($destinasi, 'destinasi')->sequence(fn ($sequence) => ['nama' => 'Pantai '.$sequence->index, 'urutan' => 1])->create();
    TempatWisata::factory()->create(['nama' => 'Pantai lain']);
    TempatWisata::factory()->for($destinasi, 'destinasi')->create(['nama' => 'Gunung', 'lokasi' => 'Pantai']);
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.tempat-wisata.index', ['search' => 'Pantai', 'destinasi_id' => $destinasi->id]))->assertInertia(fn (Assert $page) => $page
        ->component('admin/tempat-wisata/index')->has('tempatWisata.data', 15)->where('tempatWisata.total', 16)
        ->where('tempatWisata.data.0.id', $matches->first()->id)->where('tempatWisata.data.0.destinasi.nama', $destinasi->nama)
        ->where('tempatWisata.next_page_url', fn (string $url): bool => str_contains($url, 'search=Pantai') && str_contains($url, 'destinasi_id='.$destinasi->id) && str_contains($url, 'page=2')));
    $this->get(route('admin.tempat-wisata.index', ['search' => 'Pantai', 'destinasi_id' => $destinasi->id, 'page' => 2]))->assertInertia(fn (Assert $page) => $page
        ->has('tempatWisata.data', 1)->where('tempatWisata.data.0.id', $matches->last()->id));
});

test('search treats wildcards and SQL fragments as literal text', function (string $search) {
    $match = TempatWisata::factory()->create(['nama' => 'Wisata '.$search]);
    TempatWisata::factory()->create(['nama' => 'Wisata biasa']);

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.tempat-wisata.index', ['search' => $search]))->assertInertia(fn (Assert $page) => $page
        ->has('tempatWisata.data', 1)->where('tempatWisata.data.0.id', $match->id));
})->with(['%', '_', '!', "' OR 1=1 --"]);

test('list orders by urutan and supports empty results', function () {
    TempatWisata::factory()->create(['urutan' => 10]);
    $first = TempatWisata::factory()->create(['urutan' => 0]);
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.tempat-wisata.index'))->assertInertia(fn (Assert $page) => $page->where('tempatWisata.data.0.id', $first->id));
    $this->get(route('admin.tempat-wisata.index', ['search' => 'not-found-xyz']))->assertInertia(fn (Assert $page) => $page->has('tempatWisata.data', 0));
});

test('invalid inputs do not create or modify tempat wisata', function (string $field, mixed $value) {
    $record = TempatWisata::factory()->create();
    $original = $record->getAttributes();
    $this->actingAs(User::factory()->admin()->create());
    $data = [...wisataInput($record->destinasi_id), $field => $value];

    $this->post(route('admin.tempat-wisata.store'), $data)->assertSessionHasErrors($field);
    $this->put(route('admin.tempat-wisata.update', $record), $data)->assertSessionHasErrors($field);

    $this->assertDatabaseCount('tempat_wisata', 1);
    $this->assertDatabaseHas('tempat_wisata', $original);
})->with([
    'missing destination' => ['destinasi_id', null],
    'invalid destination' => ['destinasi_id', 99999],
    'blank name' => ['nama', '  '],
    'name too long' => ['nama', str_repeat('a', 256)],
    'slug injection' => ['slug', 'manual'],
    'short description' => ['deskripsi_singkat', str_repeat('a', 1001)],
    'long description' => ['deskripsi_lengkap', str_repeat('a', 50001)],
    'location' => ['lokasi', str_repeat('a', 256)],
    'opening hours' => ['jam_operasional', str_repeat('a', 5001)],
    'best time' => ['waktu_terbaik', str_repeat('a', 5001)],
    'tips' => ['tips', str_repeat('a', 5001)],
    'negative price' => ['harga_tiket', -1],
    'precision price' => ['harga_tiket', '1.234'],
    'overflow price' => ['harga_tiket', '10000000000'],
    'non numeric price' => ['harga_tiket', 'gratis'],
    'status' => ['status', 'active'],
    'featured' => ['unggulan', 'yes'],
    'negative order' => ['urutan', -1],
    'fraction order' => ['urutan', 1.5],
    'overflow order' => ['urutan', 4294967296],
]);

test('invalid and duplicate relation ids are rejected', function (string $relation, string $class) {
    $record = TempatWisata::factory()->create();
    $master = $class::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.tempat-wisata.store'), [...wisataInput($record->destinasi_id), $relation => [99999]])->assertSessionHasErrors($relation.'.0');
    $this->put(route('admin.tempat-wisata.update', $record), [...wisataInput($record->destinasi_id), $relation => [$master->id, $master->id]])->assertSessionHasErrors($relation.'.0');

    $this->assertDatabaseCount($relation.'_tempat_wisata', 0);
    $this->assertDatabaseCount('tempat_wisata', 1);
})->with([['pengalaman', Pengalaman::class], ['aktivitas', Aktivitas::class], ['fasilitas', Fasilitas::class]]);

test('optional text normalizes to null and protected attributes are ignored', function () {
    $destinasi = Destinasi::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), [
        ...wisataInput($destinasi->id), 'nama' => '  Pantai  ', 'lokasi' => '  ', 'harga_tiket' => '', 'id' => 98765,
    ])->assertSessionHasNoErrors();

    $record = TempatWisata::query()->sole();
    expect($record->id)->not->toBe(98765);
    $this->assertDatabaseHas('tempat_wisata', ['nama' => 'Pantai', 'lokasi' => null, 'harga_tiket' => null]);
});

test('deleting tempat wisata detaches pivots but preserves master records', function () {
    $record = TempatWisata::factory()->create();
    $pengalaman = Pengalaman::factory()->create();
    $aktivitas = Aktivitas::factory()->create();
    $fasilitas = Fasilitas::factory()->create();
    $record->pengalaman()->attach($pengalaman);
    $record->aktivitas()->attach($aktivitas);
    $record->fasilitas()->attach($fasilitas);

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.tempat-wisata.destroy', $record))->assertRedirect(route('admin.tempat-wisata.index'));

    $this->assertModelMissing($record);
    $this->assertDatabaseCount('pengalaman_tempat_wisata', 0);
    $this->assertDatabaseCount('aktivitas_tempat_wisata', 0);
    $this->assertDatabaseCount('fasilitas_tempat_wisata', 0);
    $this->assertModelExists($pengalaman);
    $this->assertModelExists($aktivitas);
    $this->assertModelExists($fasilitas);
    $this->assertModelExists($record->destinasi);
});

test('a destination with tempat wisata cannot be deleted through the existing CRUD', function () {
    $record = TempatWisata::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $record->destinasi_id))->assertSessionHasErrors('destinasi');

    $this->assertModelExists($record);
    $this->assertModelExists($record->destinasi);
});

test('missing tempat wisata returns not found', function (string $method, string $action) {
    $this->actingAs(User::factory()->admin()->create())->call($method, route('admin.tempat-wisata.'.$action, 999), [])->assertNotFound();
})->with([['GET', 'show'], ['GET', 'edit'], ['PUT', 'update'], ['DELETE', 'destroy']]);

test('invalid search and filters are rejected', function (array $query, string $field) {
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.tempat-wisata.index', $query))->assertSessionHasErrors($field);
})->with([
    'search' => [['search' => str_repeat('a', 256)], 'search'],
    'destination' => [['destinasi_id' => 99999], 'destinasi_id'],
    'page' => [['page' => 0], 'page'],
]);

test('repeated slug collisions return a validation error', function () {
    $destinasi = Destinasi::factory()->create();
    TempatWisata::creating(function (TempatWisata $record): void {
        DB::table('tempat_wisata')->insert(['destinasi_id' => $record->destinasi_id, 'nama' => 'Concurrent', 'slug' => $record->slug]);
    });

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), wisataInput($destinasi->id))->assertSessionHasErrors('nama');

    $this->assertDatabaseCount('tempat_wisata', 0);
});

test('other unique constraints are not disguised as slug validation', function () {
    $record = TempatWisata::factory()->create();
    Exceptions::fake();
    TempatWisata::creating(function (TempatWisata $new) use ($record): void {
        $new->id = $record->id;
    });

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), wisataInput($record->destinasi_id))->assertServerError();

    $this->assertDatabaseCount('tempat_wisata', 1);
    Exceptions::assertReported(UniqueConstraintViolationException::class);
});
