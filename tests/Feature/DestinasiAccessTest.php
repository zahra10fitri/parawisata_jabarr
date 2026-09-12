<?php

use App\Models\Destinasi;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

dataset('destinasi endpoints', [
    'list' => ['GET', 'index'],
    'create' => ['GET', 'create'],
    'store' => ['POST', 'store'],
    'show' => ['GET', 'show'],
    'edit' => ['GET', 'edit'],
    'update put' => ['PUT', 'update'],
    'update patch' => ['PATCH', 'update'],
    'delete' => ['DELETE', 'destroy'],
]);

test('guests must log in for every destinasi endpoint', function (string $method, string $action) {
    $destinasi = Destinasi::factory()->create();

    $this->call($method, route('admin.destinasi.'.$action, $destinasi))->assertRedirect(route('login'));

    $this->assertModelExists($destinasi);
})->with('destinasi endpoints');

test('nonadmins cannot read or mutate destinasi', function (string $method, string $action) {
    $disk = Storage::fake('public');
    $disk->put('destinasi/existing.jpg', 'existing');
    $destinasi = Destinasi::factory()->create(['foto_utama' => 'destinasi/existing.jpg']);
    $original = $destinasi->getAttributes();

    $this->actingAs(User::factory()->create())
        ->call($method, route('admin.destinasi.'.$action, $destinasi), [
            'nama' => 'Unauthorized change', 'status' => true, 'unggulan' => true, 'urutan' => 1,
        ])->assertForbidden();

    $this->assertDatabaseHas('destinasi', $original);
    $this->assertDatabaseCount('destinasi', 1);
    $disk->assertExists('destinasi/existing.jpg');
})->with('destinasi endpoints');

test('revoked admin sessions cannot access destinasi', function (string $method, string $action) {
    $admin = User::factory()->admin()->create();
    $destinasi = Destinasi::factory()->create();
    $this->actingAs($admin);
    $admin->forceFill(['is_admin' => false])->save();
    Auth::forgetGuards();

    $this->actingAs($admin->fresh())->call($method, route('admin.destinasi.'.$action, $destinasi))->assertForbidden();

    $this->assertModelExists($destinasi);
})->with('destinasi endpoints');
