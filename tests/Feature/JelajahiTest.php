<?php

use App\Models\Aktivitas;
use App\Models\Destinasi;
use App\Models\Fasilitas;
use App\Models\GaleriTempatWisata;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

test('guests can explore only active places ordered by featured then manual order and id', function () {
    TempatWisata::factory()->unggulan()->create(['nama' => 'Hidden']);
    $regular = TempatWisata::factory()->aktif()->create(['urutan' => 0]);
    $featuredLast = TempatWisata::factory()->aktif()->unggulan()->create(['urutan' => 9]);
    $featuredFirst = TempatWisata::factory()->aktif()->unggulan()->create(['urutan' => 1]);
    $featuredTie = TempatWisata::factory()->aktif()->unggulan()->create(['urutan' => 1]);

    $this->get(route('jelajahi.index'))->assertInertia(fn (Assert $page) => $page
        ->component('jelajahi/index')->has('wisata.data', 4)
        ->where('wisata.data.0.id', $featuredFirst->id)
        ->where('wisata.data.1.id', $featuredTie->id)
        ->where('wisata.data.2.id', $featuredLast->id)
        ->where('wisata.data.3.id', $regular->id)
        ->missing('wisata.data.0.status')->missing('wisata.data.0.foto_utama'));
});

test('search filters only place names and cannot expose inactive places', function () {
    $match = TempatWisata::factory()->aktif()->create(['nama' => 'Pantai Batu Hiu']);
    TempatWisata::factory()->create(['nama' => 'Pantai rahasia']);
    TempatWisata::factory()->aktif()->create(['nama' => 'Gunung', 'lokasi' => 'Pantai']);

    $this->get(route('jelajahi.index', ['search' => 'Pantai']))->assertInertia(fn (Assert $page) => $page
        ->has('wisata.data', 1)->where('wisata.data.0.id', $match->id)->where('filters.search', 'Pantai'));
});

test('search treats SQL fragments and wildcard characters literally', function (string $search) {
    $match = TempatWisata::factory()->aktif()->create(['nama' => 'Wisata '.$search]);
    TempatWisata::factory()->aktif()->create(['nama' => 'Wisata biasa']);

    $this->get(route('jelajahi.index', ['search' => $search]))->assertInertia(fn (Assert $page) => $page
        ->has('wisata.data', 1)->where('wisata.data.0.id', $match->id));
})->with(['%', '_', '!', "' OR 1=1 --"]);

test('destination and experience filters combine with search and preserve pagination', function () {
    $destinasi = Destinasi::factory()->create();
    $pengalaman = Pengalaman::factory()->create();
    $otherExperience = Pengalaman::factory()->create();
    $matches = TempatWisata::factory()->aktif()->count(13)->for($destinasi, 'destinasi')
        ->hasAttached($pengalaman, [], 'pengalaman')->hasAttached($otherExperience, [], 'pengalaman')
        ->sequence(fn ($sequence) => ['nama' => 'Pantai '.$sequence->index])->create();
    TempatWisata::factory()->aktif()->hasAttached($pengalaman, [], 'pengalaman')->create(['nama' => 'Pantai lain']);
    TempatWisata::factory()->aktif()->for($destinasi, 'destinasi')->create(['nama' => 'Pantai tanpa pengalaman']);
    TempatWisata::factory()->for($destinasi, 'destinasi')->hasAttached($pengalaman, [], 'pengalaman')->create(['nama' => 'Pantai nonaktif']);
    $filters = ['search' => 'Pantai', 'destinasi_id' => $destinasi->id, 'pengalaman_id' => $pengalaman->id];

    $this->get(route('jelajahi.index', $filters))->assertInertia(fn (Assert $page) => $page
        ->has('wisata.data', 12)->where('wisata.total', 13)
        ->where('wisata.data.0.id', $matches->first()->id)
        ->where('wisata.next_page_url', fn (string $url): bool => str_contains($url, 'search=Pantai')
            && str_contains($url, 'destinasi_id='.$destinasi->id) && str_contains($url, 'pengalaman_id='.$pengalaman->id) && str_contains($url, 'page=2')));
    $this->get(route('jelajahi.index', [...$filters, 'page' => 2]))->assertInertia(fn (Assert $page) => $page
        ->has('wisata.data', 1)->where('wisata.data.0.id', $matches->last()->id));
});

test('each filter works independently', function (string $filter) {
    $destinasi = Destinasi::factory()->create();
    $pengalaman = Pengalaman::factory()->create();
    $match = TempatWisata::factory()->aktif()->for($destinasi, 'destinasi')->hasAttached($pengalaman, [], 'pengalaman')->create();
    TempatWisata::factory()->aktif()->create();
    $id = $filter === 'destinasi_id' ? $destinasi->id : $pengalaman->id;

    $this->get(route('jelajahi.index', [$filter => $id]))->assertInertia(fn (Assert $page) => $page
        ->has('wisata.data', 1)->where('wisata.data.0.id', $match->id));
})->with(['destinasi_id', 'pengalaman_id']);

test('an empty result keeps the selected filters and available master options', function () {
    $destinasi = Destinasi::factory()->create();
    $pengalaman = Pengalaman::factory()->create();

    $this->get(route('jelajahi.index', ['search' => 'nothing', 'destinasi_id' => $destinasi->id, 'pengalaman_id' => $pengalaman->id]))
        ->assertInertia(fn (Assert $page) => $page->has('wisata.data', 0)->where('wisata.total', 0)
            ->where('filters.destinasi_id', (string) $destinasi->id)->where('filters.pengalaman_id', (string) $pengalaman->id)
            ->where('destinasiOptions.0.nama', $destinasi->nama)->where('pengalamanOptions.0.nama', $pengalaman->nama));
});

test('public detail resolves an active slug and exposes the complete visitor information', function () {
    $disk = Storage::fake('public');
    $record = TempatWisata::factory()->aktif()->create([
        'slug' => 'pantai-batu-hiu', 'foto_utama' => 'tempat-wisata/main.jpg',
        'deskripsi_singkat' => 'Pantai indah', 'deskripsi_lengkap' => 'Deskripsi lengkap',
        'lokasi' => 'Pangandaran', 'jam_operasional' => '08:00–17:00', 'harga_tiket' => 15000,
        'waktu_terbaik' => 'Pagi', 'tips' => 'Bawa topi',
    ]);
    $pengalaman = Pengalaman::factory()->create();
    $aktivitas = Aktivitas::factory()->create();
    $fasilitas = Fasilitas::factory()->create();
    $record->pengalaman()->attach($pengalaman);
    $record->aktivitas()->attach($aktivitas);
    $record->fasilitas()->attach($fasilitas);
    $last = GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['urutan' => 5]);
    $first = GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['urutan' => 0]);

    $this->get(route('jelajahi.show', $record->slug))->assertInertia(fn (Assert $page) => $page
        ->component('jelajahi/show')->where('wisata.nama', $record->nama)->where('wisata.slug', 'pantai-batu-hiu')
        ->where('wisata.destinasi.nama', $record->destinasi->nama)
        ->where('wisata.deskripsi_singkat', 'Pantai indah')->where('wisata.deskripsi_lengkap', 'Deskripsi lengkap')
        ->where('wisata.lokasi', 'Pangandaran')->where('wisata.jam_operasional', '08:00–17:00')
        ->where('wisata.harga_tiket', '15000.00')->where('wisata.waktu_terbaik', 'Pagi')->where('wisata.tips', 'Bawa topi')
        ->where('wisata.foto_url', $disk->url('tempat-wisata/main.jpg'))
        ->where('wisata.pengalaman.0.id', $pengalaman->id)->where('wisata.aktivitas.0.id', $aktivitas->id)->where('wisata.fasilitas.0.id', $fasilitas->id)
        ->where('wisata.galeri.0.id', $first->id)->where('wisata.galeri.0.url', $disk->url($first->foto))->where('wisata.galeri.1.id', $last->id)
        ->missing('wisata.status')->missing('wisata.foto_utama')->missing('wisata.galeri.0.foto')->missing('wisata.pengalaman.0.pivot'));

    expect($disk->allFiles())->toBe([]);
});

test('inactive places cannot be opened publicly even by an admin', function () {
    $record = TempatWisata::factory()->create(['slug' => 'hidden-place']);

    $this->get(route('jelajahi.show', $record->slug))->assertNotFound();
    $this->actingAs(User::factory()->admin()->create())->get(route('jelajahi.show', $record->slug))->assertNotFound();
});

test('missing slugs and numeric record ids do not resolve as public detail', function () {
    $record = TempatWisata::factory()->aktif()->create(['slug' => 'published-place']);

    $this->get(route('jelajahi.show', 'does-not-exist'))->assertNotFound();
    $this->get(route('jelajahi.show', (string) $record->id))->assertNotFound();
});

test('unpublishing a place immediately removes both its listing and public detail', function () {
    $record = TempatWisata::factory()->aktif()->create();
    $record->update(['status' => false]);

    $this->get(route('jelajahi.index'))->assertInertia(fn (Assert $page) => $page->has('wisata.data', 0));
    $this->get(route('jelajahi.show', $record->slug))->assertNotFound();
});

test('optional content and photos can be empty without breaking public detail', function () {
    $record = TempatWisata::factory()->aktif()->create();

    $this->get(route('jelajahi.show', $record->slug))->assertInertia(fn (Assert $page) => $page
        ->where('wisata.foto_url', null)->where('wisata.harga_tiket', null)
        ->where('wisata.deskripsi_lengkap', null)->where('wisata.tips', null)
        ->has('wisata.galeri', 0)->has('wisata.pengalaman', 0)->has('wisata.aktivitas', 0)->has('wisata.fasilitas', 0));
});

test('invalid public query parameters are rejected', function (array $query, string $field) {
    $this->get(route('jelajahi.index', $query))->assertSessionHasErrors($field);
})->with([
    'long search' => [['search' => str_repeat('a', 256)], 'search'],
    'array search' => [['search' => ['invalid']], 'search'],
    'unknown destination' => [['destinasi_id' => 99999], 'destinasi_id'],
    'unknown experience' => [['pengalaman_id' => 99999], 'pengalaman_id'],
    'invalid page' => [['page' => 0], 'page'],
]);

test('listing eager loads destinations and experiences without queries per card', function () {
    TempatWisata::factory()->aktif()->count(12)->hasAttached(Pengalaman::factory(), [], 'pengalaman')->create();
    DB::enableQueryLog();

    $this->get(route('jelajahi.index'))->assertInertia(fn (Assert $page) => $page->has('wisata.data', 12)->has('wisata.data.0.pengalaman', 1));

    $queries = collect(DB::getQueryLog())->pluck('query');
    DB::disableQueryLog();
    expect($queries->filter(fn (string $sql): bool => str_contains($sql, 'from "destinasi"')))->toHaveCount(2);
    expect($queries->filter(fn (string $sql): bool => str_contains($sql, 'from "pengalaman"')))->toHaveCount(2);
});
