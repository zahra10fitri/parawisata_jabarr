<?php

use App\Models\Destinasi;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

/** @return array{nama: string, status: bool, unggulan: bool, urutan: int} */
function destinasiPhotoInput(): array
{
    return ['nama' => 'Pantai', 'status' => false, 'unggulan' => true, 'urutan' => 0];
}

test('uploaded photos use relative paths on the public disk', function (string $extension) {
    $disk = Storage::fake('public');

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.store'), [
        ...destinasiPhotoInput(), 'foto_utama' => UploadedFile::fake()->image('original.'.$extension),
    ])->assertSessionHasNoErrors();

    $record = Destinasi::query()->sole();
    expect($record->foto_utama)->toStartWith('destinasi/')->not->toContain('original');
    $disk->assertExists($record->foto_utama);
    $this->get(route('admin.destinasi.show', $record))->assertInertia(fn (Assert $page) => $page
        ->where('destinasi.foto_utama_url', $disk->url($record->foto_utama)));
})->with(['jpg', 'png', 'webp']);

test('replacing a photo deletes the old file after updating the record', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.update', $record), [
        ...destinasiPhotoInput(), '_method' => 'put', 'foto_utama' => UploadedFile::fake()->image('new.png'),
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.destinasi.show', $record));

    $disk->assertMissing('destinasi/old.jpg');
    $disk->assertExists($record->fresh()->foto_utama);
});

test('editing without an upload retains the current photo', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);

    $this->actingAs(User::factory()->admin()->create())->put(route('admin.destinasi.update', $record), [
        ...destinasiPhotoInput(), 'foto_utama' => null,
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('destinasi', ['id' => $record->id, 'foto_utama' => 'destinasi/old.jpg']);
    $disk->assertExists('destinasi/old.jpg');
});

test('deleting a destinasi removes its photo', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $record))->assertRedirect(route('admin.destinasi.index'));

    $this->assertModelMissing($record);
    $disk->assertMissing('destinasi/old.jpg');
});

test('missing photo files do not prevent deleting a destinasi', function () {
    $disk = Storage::fake('public');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/missing.jpg']);

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $record))->assertRedirect();

    $this->assertModelMissing($record);
    $disk->assertMissing('destinasi/missing.jpg');
});

test('photos referenced by another destinasi are retained', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/shared.jpg', 'shared');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/shared.jpg']);
    $other = Destinasi::factory()->create(['foto_utama' => 'destinasi/shared.jpg']);

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $record))->assertRedirect();

    $this->assertModelMissing($record);
    $this->assertModelExists($other);
    $disk->assertExists('destinasi/shared.jpg');
});

test('cleanup refuses untrusted paths and reports a warning', function (string $path) {
    $disk = Storage::fake('public');
    $disk->put('other.jpg', 'protected');
    $record = Destinasi::factory()->create(['foto_utama' => $path]);
    Log::spy();

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $record))->assertRedirect();

    $this->assertModelMissing($record);
    $disk->assertExists('other.jpg');
    Log::shouldHaveReceived('warning')->once();
})->with(['other.jpg', 'destinasi/../other.jpg', '/destinasi/other.jpg', 'https://example.com/other.jpg']);

test('invalid photos are rejected without changing records or existing files', function (Closure $upload) {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);
    $original = $record->getAttributes();
    $this->actingAs(User::factory()->admin()->create());

    $this->post(route('admin.destinasi.store'), [...destinasiPhotoInput(), 'foto_utama' => $upload()])->assertSessionHasErrors('foto_utama');
    $this->post(route('admin.destinasi.update', $record), [...destinasiPhotoInput(), '_method' => 'put', 'foto_utama' => $upload()])->assertSessionHasErrors('foto_utama');

    $this->assertDatabaseCount('destinasi', 1);
    $this->assertDatabaseHas('destinasi', $original);
    expect($disk->allFiles())->toBe(['destinasi/old.jpg']);
})->with([
    'fake jpeg' => [fn () => UploadedFile::fake()->createWithContent('fake.jpg', 'not an image')],
    'gif' => [fn () => UploadedFile::fake()->image('animated.gif')],
    'svg' => [fn () => UploadedFile::fake()->createWithContent('vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')],
    'oversized' => [fn () => UploadedFile::fake()->image('large.jpg')->size(5121)],
    'too wide' => [fn () => UploadedFile::fake()->image('wide.png', 6001, 1)],
    'too tall' => [fn () => UploadedFile::fake()->image('tall.png', 1, 6001)],
    'path injection' => [fn () => 'destinasi/another.jpg'],
]);

test('database failure while replacing a photo keeps the old photo and removes the new upload', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);
    $original = $record->getAttributes();
    Exceptions::fake();
    Destinasi::updating(function (): void {
        throw new RuntimeException('Simulated database failure');
    });

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.update', $record), [
        ...destinasiPhotoInput(), '_method' => 'put', 'foto_utama' => UploadedFile::fake()->image('new.jpg'),
    ])->assertServerError();

    $this->assertDatabaseHas('destinasi', $original);
    expect($disk->allFiles())->toBe(['destinasi/old.jpg']);
    Exceptions::assertReported(RuntimeException::class);
});

test('database failure while creating a destinasi cleans its upload', function () {
    $disk = Storage::fake('public');
    Exceptions::fake();
    Destinasi::creating(function (): void {
        throw new RuntimeException('Simulated insert failure');
    });

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.store'), [
        ...destinasiPhotoInput(), 'foto_utama' => UploadedFile::fake()->image('new.jpg'),
    ])->assertServerError();

    $this->assertDatabaseCount('destinasi', 0);
    expect($disk->allFiles())->toBe([]);
    Exceptions::assertReported(RuntimeException::class);
});

test('database failure while deleting retains the record and photo', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);
    Exceptions::fake();
    Destinasi::deleting(function (): void {
        throw new RuntimeException('Simulated delete failure');
    });

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $record))->assertServerError();

    $this->assertModelExists($record);
    $disk->assertExists('destinasi/old.jpg');
    Exceptions::assertReported(RuntimeException::class);
});

test('failed storage writes leave the old photo and record unchanged', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);
    $original = $record->getAttributes();
    $failingDisk = Mockery::mock($disk);
    $failingDisk->shouldReceive('putFileAs')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('public')->andReturn($failingDisk);

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.destinasi.update', $record), [
        ...destinasiPhotoInput(), '_method' => 'put', 'foto_utama' => UploadedFile::fake()->image('new.jpg'),
    ])->assertSessionHasErrors(['foto_utama' => 'Foto gagal disimpan. Silakan coba kembali.']);

    $this->assertDatabaseHas('destinasi', $original);
    expect($disk->allFiles())->toBe(['destinasi/old.jpg']);
});

test('failed photo cleanup reports a warning without undoing a committed deletion', function () {
    $disk = Storage::fake('public');
    $disk->put('destinasi/old.jpg', 'old');
    $record = Destinasi::factory()->create(['foto_utama' => 'destinasi/old.jpg']);
    $failingDisk = Mockery::mock($disk);
    $failingDisk->shouldReceive('delete')->with('destinasi/old.jpg')->once()->andReturn(false);
    Storage::shouldReceive('disk')->with('public')->andReturn($failingDisk);
    Log::spy();

    $this->actingAs(User::factory()->admin()->create())->delete(route('admin.destinasi.destroy', $record))
        ->assertRedirect(route('admin.destinasi.index'))->assertInertiaFlash('toast.type', 'warning');

    $this->assertModelMissing($record);
    $disk->assertExists('destinasi/old.jpg');
    Log::shouldHaveReceived('warning')->once();
});
