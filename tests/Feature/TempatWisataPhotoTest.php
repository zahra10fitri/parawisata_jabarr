<?php

use App\Models\Destinasi;
use App\Models\GaleriTempatWisata;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

/** @return array{destinasi_id: int, nama: string, status: bool, unggulan: bool, urutan: int} */
function wisataPhotoInput(int $destinasiId): array
{
    return ['destinasi_id' => $destinasiId, 'nama' => 'Pantai', 'status' => false, 'unggulan' => false, 'urutan' => 0];
}

test('main and gallery uploads use relative public disk paths and expose display urls', function () {
    $disk = Storage::fake('public');
    $destinasi = Destinasi::factory()->create();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.tempat-wisata.store'), [
        ...wisataPhotoInput($destinasi->id),
        'foto_utama' => UploadedFile::fake()->image('main.jpg'),
        'galeri' => [UploadedFile::fake()->image('gallery.png'), UploadedFile::fake()->image('gallery.webp')],
    ])->assertSessionHasNoErrors();

    $record = TempatWisata::query()->sole();
    $record->load('galeri');
    expect($record->foto_utama)->toStartWith('tempat-wisata/');
    $disk->assertExists($record->foto_utama);
    expect($record->galeri)->toHaveCount(2);
    foreach ($record->galeri as $photo) {
        expect($photo->foto)->toStartWith('tempat-wisata/');
        $disk->assertExists($photo->foto);
    }
    expect($disk->allFiles())->toHaveCount(3);
    $this->get(route('admin.tempat-wisata.show', $record))->assertInertia(fn (Assert $page) => $page
        ->where('tempatWisata.foto_utama_url', $disk->url($record->foto_utama))
        ->has('tempatWisata.galeri', 2)->where('tempatWisata.galeri.0.url', $disk->url($record->galeri[0]->foto)));
});

test('edit replaces main photo removes selected gallery and preserves unselected files', function () {
    $disk = Storage::fake('public');
    foreach (['main', 'remove', 'keep'] as $name) {
        $disk->put('tempat-wisata/'.$name.'.jpg', $name);
    }
    $record = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/main.jpg']);
    $remove = GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['foto' => 'tempat-wisata/remove.jpg']);
    $keep = GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['foto' => 'tempat-wisata/keep.jpg']);

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.update', $record), [
        ...wisataPhotoInput($record->destinasi_id), '_method' => 'put',
        'foto_utama' => UploadedFile::fake()->image('new.jpg'), 'galeri' => [UploadedFile::fake()->image('new-gallery.png')],
        'hapus_galeri' => [$remove->id],
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.tempat-wisata.show', $record));

    $disk->assertMissing(['tempat-wisata/main.jpg', 'tempat-wisata/remove.jpg']);
    $disk->assertExists(['tempat-wisata/keep.jpg', $record->fresh()->foto_utama]);
    $this->assertModelMissing($remove);
    $this->assertModelExists($keep);
    $this->assertDatabaseCount('galeri_tempat_wisata', 2);
    expect($disk->allFiles())->toHaveCount(3);
});

test('edit without uploads or removals preserves every current photo', function () {
    $disk = Storage::fake('public');
    $disk->put('tempat-wisata/main.jpg', 'main');
    $disk->put('tempat-wisata/gallery.jpg', 'gallery');
    $record = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/main.jpg']);
    $photo = GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['foto' => 'tempat-wisata/gallery.jpg']);

    $this->actingAs(User::factory()->admin()->create())->put(route('admin.tempat-wisata.update', $record), wisataPhotoInput($record->destinasi_id))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tempat_wisata', ['id' => $record->id, 'foto_utama' => 'tempat-wisata/main.jpg']);
    $this->assertModelExists($photo);
    $disk->assertExists(['tempat-wisata/main.jpg', 'tempat-wisata/gallery.jpg']);
});

test('deleting tempat wisata removes its main and gallery files', function () {
    $disk = Storage::fake('public');
    $disk->put('tempat-wisata/main.jpg', 'main');
    $disk->put('tempat-wisata/gallery.jpg', 'gallery');
    $record = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/main.jpg']);
    GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['foto' => 'tempat-wisata/gallery.jpg']);

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.tempat-wisata.destroy', $record))->assertRedirect(route('admin.tempat-wisata.index'));

    $this->assertModelMissing($record);
    $this->assertDatabaseCount('galeri_tempat_wisata', 0);
    expect($disk->allFiles())->toBe([]);
});

test('gallery ids from another tempat wisata cannot be removed', function () {
    $disk = Storage::fake('public');
    $disk->put('tempat-wisata/other.jpg', 'other');
    $record = TempatWisata::factory()->create();
    $otherPhoto = GaleriTempatWisata::factory()->create(['foto' => 'tempat-wisata/other.jpg']);

    $this->actingAs(User::factory()->admin()->create())->put(route('admin.tempat-wisata.update', $record), [
        ...wisataPhotoInput($record->destinasi_id), 'hapus_galeri' => [$otherPhoto->id],
    ])->assertSessionHasErrors('hapus_galeri.0');

    $this->assertModelExists($otherPhoto);
    $disk->assertExists('tempat-wisata/other.jpg');
    $this->assertDatabaseHas('tempat_wisata', ['id' => $record->id, 'nama' => $record->nama]);
});

test('gallery removals on create are rejected', function () {
    $photo = GaleriTempatWisata::factory()->create();
    $record = $photo->tempatWisata;

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), [
        ...wisataPhotoInput($record->destinasi_id), 'hapus_galeri' => [$photo->id],
    ])->assertSessionHasErrors('hapus_galeri');

    $this->assertModelExists($photo);
    $this->assertDatabaseCount('tempat_wisata', 1);
});

test('invalid main or gallery uploads never reach storage', function (string $field, Closure $upload) {
    $disk = Storage::fake('public');
    $record = TempatWisata::factory()->create();
    $file = $upload();
    $data = $field === 'foto_utama' ? ['foto_utama' => $file] : ['galeri' => [$file]];

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), [...wisataPhotoInput($record->destinasi_id), ...$data])->assertSessionHasErrors($field);

    $this->assertDatabaseCount('tempat_wisata', 1);
    expect($disk->allFiles())->toBe([]);
})->with(['foto_utama', 'galeri.0'])->with([
    'fake image' => [fn () => UploadedFile::fake()->createWithContent('fake.jpg', 'not an image')],
    'svg' => [fn () => UploadedFile::fake()->createWithContent('image.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')],
    'oversized' => [fn () => UploadedFile::fake()->image('big.jpg')->size(5121)],
    'dimensions' => [fn () => UploadedFile::fake()->image('wide.png', 6001, 1)],
]);

test('gallery request upload count is limited', function () {
    $disk = Storage::fake('public');
    $record = TempatWisata::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.store'), [
        ...wisataPhotoInput($record->destinasi_id),
        'galeri' => array_map(fn (int $i): UploadedFile => UploadedFile::fake()->image('photo-'.$i.'.jpg'), range(1, 11)),
    ])->assertSessionHasErrors('galeri');

    $this->assertDatabaseCount('tempat_wisata', 1);
    expect($disk->allFiles())->toBe([]);
});

test('gallery total limit is enforced and staged uploads are cleaned', function () {
    $disk = Storage::fake('public');
    $record = TempatWisata::factory()->create();
    GaleriTempatWisata::factory()->count(20)->for($record, 'tempatWisata')->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.update', $record), [
        ...wisataPhotoInput($record->destinasi_id), '_method' => 'put', 'galeri' => [UploadedFile::fake()->image('extra.jpg')],
    ])->assertSessionHasErrors('galeri');

    $this->assertDatabaseCount('galeri_tempat_wisata', 20);
    expect($disk->allFiles())->toBe([]);
});

test('a full gallery can replace a selected photo in the same transaction', function () {
    $disk = Storage::fake('public');
    $record = TempatWisata::factory()->create();
    $photos = GaleriTempatWisata::factory()->count(20)->for($record, 'tempatWisata')->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.update', $record), [
        ...wisataPhotoInput($record->destinasi_id), '_method' => 'put',
        'galeri' => [UploadedFile::fake()->image('new.jpg')], 'hapus_galeri' => [$photos[0]->id],
    ])->assertSessionHasNoErrors();

    $this->assertModelMissing($photos[0]);
    $this->assertDatabaseCount('galeri_tempat_wisata', 20);
    expect($disk->allFiles())->toHaveCount(1);
});

test('database failure rolls back content relations and gallery and cleans every staged upload', function () {
    $disk = Storage::fake('public');
    $disk->put('tempat-wisata/main.jpg', 'main');
    $disk->put('tempat-wisata/old.jpg', 'old');
    $record = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/main.jpg']);
    $original = $record->getAttributes();
    $photo = GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['foto' => 'tempat-wisata/old.jpg']);
    $oldMaster = Pengalaman::factory()->create();
    $newMaster = Pengalaman::factory()->create();
    $record->pengalaman()->attach($oldMaster);
    Exceptions::fake();
    GaleriTempatWisata::creating(function (): void {
        throw new RuntimeException('Simulated gallery insert failure');
    });

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.update', $record), [
        ...wisataPhotoInput($record->destinasi_id), '_method' => 'put',
        'foto_utama' => UploadedFile::fake()->image('main.jpg'), 'galeri' => [UploadedFile::fake()->image('new.jpg')],
        'hapus_galeri' => [$photo->id], 'pengalaman' => [$newMaster->id],
    ])->assertServerError();

    $this->assertDatabaseHas('tempat_wisata', $original);
    $this->assertModelExists($photo);
    expect($record->pengalaman()->pluck('pengalaman.id')->all())->toBe([$oldMaster->id]);
    expect($disk->allFiles())->toBe(['tempat-wisata/main.jpg', 'tempat-wisata/old.jpg']);
    Exceptions::assertReported(RuntimeException::class);
});

test('failed deletion keeps the record and all photos', function () {
    $disk = Storage::fake('public');
    $disk->put('tempat-wisata/main.jpg', 'main');
    $record = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/main.jpg']);
    $photo = GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create();
    Exceptions::fake();
    TempatWisata::deleting(function (): void {
        throw new RuntimeException('Simulated delete failure');
    });

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.tempat-wisata.destroy', $record))->assertServerError();

    $this->assertModelExists($record);
    $this->assertModelExists($photo);
    $disk->assertExists('tempat-wisata/main.jpg');
    Exceptions::assertReported(RuntimeException::class);
});

test('missing files do not block deletion and shared files remain referenced', function () {
    $disk = Storage::fake('public');
    $disk->put('tempat-wisata/shared.jpg', 'shared');
    $record = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/missing.jpg']);
    GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['foto' => 'tempat-wisata/shared.jpg']);
    $other = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/shared.jpg']);

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.tempat-wisata.destroy', $record))->assertRedirect();

    $this->assertModelMissing($record);
    $this->assertModelExists($other);
    $disk->assertExists('tempat-wisata/shared.jpg');
});

test('cleanup refuses paths outside its managed directory', function (string $path) {
    $disk = Storage::fake('public');
    $disk->put('destinasi/protected.jpg', 'protected');
    $record = TempatWisata::factory()->create(['foto_utama' => $path]);
    Log::spy();

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.tempat-wisata.destroy', $record))
        ->assertRedirect()->assertInertiaFlash('toast.type', 'warning');

    $this->assertModelMissing($record);
    $disk->assertExists('destinasi/protected.jpg');
    Log::shouldHaveReceived('warning')->once();
})->with(['destinasi/protected.jpg', 'tempat-wisata/../destinasi/protected.jpg', 'https://example.com/photo.jpg']);

test('failed gallery upload cleans earlier uploads without changing database content', function () {
    $disk = Storage::fake('public');
    $record = TempatWisata::factory()->create();
    $failingDisk = Mockery::mock($disk);
    $failingDisk->shouldReceive('putFileAs')->once()->andReturnUsing(fn (...$args) => $disk->putFileAs(...$args));
    $failingDisk->shouldReceive('putFileAs')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('public')->andReturn($failingDisk);
    Exceptions::fake();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.tempat-wisata.update', $record), [
        ...wisataPhotoInput($record->destinasi_id), '_method' => 'put',
        'foto_utama' => UploadedFile::fake()->image('main.jpg'), 'galeri' => [UploadedFile::fake()->image('gallery.jpg')],
    ])->assertSessionHasErrors('galeri.0');

    $this->assertDatabaseHas('tempat_wisata', ['id' => $record->id, 'foto_utama' => null, 'nama' => $record->nama]);
    expect($disk->allFiles())->toBe([]);
    Exceptions::assertReported(RuntimeException::class);
});

test('cleanup failure reports a warning and continues deleting other files', function () {
    $disk = Storage::fake('public');
    $disk->put('tempat-wisata/fail.jpg', 'fail');
    $disk->put('tempat-wisata/main.jpg', 'main');
    $record = TempatWisata::factory()->create(['foto_utama' => 'tempat-wisata/main.jpg']);
    GaleriTempatWisata::factory()->for($record, 'tempatWisata')->create(['foto' => 'tempat-wisata/fail.jpg']);
    $failingDisk = Mockery::mock($disk);
    $failingDisk->shouldReceive('delete')->with('tempat-wisata/fail.jpg')->once()->andReturn(false);
    $failingDisk->shouldReceive('delete')->with('tempat-wisata/main.jpg')->once()
        ->andReturnUsing(fn (string $path): bool => $disk->delete($path));
    Storage::shouldReceive('disk')->with('public')->andReturn($failingDisk);
    Log::spy();

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.tempat-wisata.destroy', $record))
        ->assertRedirect()->assertInertiaFlash('toast.type', 'warning');

    $this->assertModelMissing($record);
    $disk->assertExists('tempat-wisata/fail.jpg');
    $disk->assertMissing('tempat-wisata/main.jpg');
    Log::shouldHaveReceived('warning')->once();
});
