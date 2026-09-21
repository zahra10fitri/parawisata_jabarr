<?php

use App\Models\TempatWisata;
use App\Models\User;

dataset('tempat wisata endpoints', [
    'list' => ['GET', 'index'], 'create' => ['GET', 'create'],
    'store' => ['POST', 'store'], 'show' => ['GET', 'show'],
    'edit' => ['GET', 'edit'], 'update put' => ['PUT', 'update'],
    'update patch' => ['PATCH', 'update'], 'delete' => ['DELETE', 'destroy'],
]);

test('guests must log in for every tempat wisata endpoint', function (string $method, string $action) {
    $record = TempatWisata::factory()->create();

    $this->call($method, route('admin.tempat-wisata.'.$action, $record))->assertRedirect(route('login'));

    $this->assertModelExists($record);
})->with('tempat wisata endpoints');

test('nonadmins cannot read or mutate tempat wisata', function (string $method, string $action) {
    $record = TempatWisata::factory()->create();
    $original = $record->getAttributes();

    $this->actingAs(User::factory()->create())->call($method, route('admin.tempat-wisata.'.$action, $record), [
        'destinasi_id' => $record->destinasi_id, 'nama' => 'Unauthorized', 'status' => true, 'unggulan' => true, 'urutan' => 0,
    ])->assertForbidden();

    $this->assertDatabaseHas('tempat_wisata', $original);
    $this->assertDatabaseCount('tempat_wisata', 1);
})->with('tempat wisata endpoints');

test('revoked admin rights block tempat wisata deletion', function () {
    $admin = User::factory()->admin()->create();
    $record = TempatWisata::factory()->create();
    $this->actingAs($admin);
    $admin->forceFill(['is_admin' => false])->save();

    $this->delete(route('admin.tempat-wisata.destroy', $record))->assertForbidden();

    $this->assertModelExists($record);
});
