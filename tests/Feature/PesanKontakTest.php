<?php

use App\Models\Destinasi;
use App\Models\PesanKontak;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->withoutVite();
});

function pesanKontakInput(): array
{
    return ['nama' => 'Nadia Rahma', 'email' => 'nadia@example.com', 'whatsapp' => '+62 812-3456-7890', 'subjek' => 'Rencana perjalanan keluarga', 'pesan' => 'Saya ingin bertanya tentang rekomendasi perjalanan keluarga di Jawa Barat.'];
}

test('public can visit about and contact pages', function (): void {
    Destinasi::factory()->create(['status' => true, 'unggulan' => true, 'foto_utama' => 'destinasi/hero.jpg']);
    $this->get(route('tentang'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('tentang')->where('heroImage', 'http://localhost:8000/storage/destinasi/hero.jpg'));
    $this->get(route('kontak.create'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('kontak'));
});

test('public can submit a contact message', function (): void {
    $this->post(route('kontak.store'), pesanKontakInput())->assertSessionHasNoErrors()->assertRedirect(route('kontak.create'))->assertInertiaFlash('toast.type', 'success');
    $this->assertDatabaseHas('pesan_kontak', ['nama' => 'Nadia Rahma', 'email' => 'nadia@example.com', 'subjek' => 'Rencana perjalanan keluarga', 'dibaca_pada' => null]);
});

test('contact message validates required and contact fields', function (array $changes, string $field): void {
    $this->post(route('kontak.store'), [...pesanKontakInput(), ...$changes])->assertSessionHasErrors($field);
    $this->assertDatabaseCount('pesan_kontak', 0);
})->with([
    'name required' => [['nama' => ''], 'nama'],
    'email valid' => [['email' => 'bukan-email'], 'email'],
    'optional whatsapp valid when present' => [['whatsapp' => 'nomor salah'], 'whatsapp'],
    'subject required' => [['subjek' => ''], 'subjek'],
    'message required' => [['pesan' => ''], 'pesan'],
]);

test('admins can search and filter contact messages with pagination', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    PesanKontak::factory()->count(16)->create(['subjek' => 'Pertanyaan umum']);
    $target = PesanKontak::factory()->create(['nama' => 'Pencari Khusus', 'subjek' => 'Paket khusus']);
    PesanKontak::factory()->create(['nama' => 'Sudah Dibaca', 'dibaca_pada' => now()]);
    $this->get(route('admin.pesan-kontak.index', ['search' => 'Pencari Khusus', 'status' => 'belum_dibaca']))->assertInertia(fn (Assert $page) => $page->component('admin/pesan-kontak/index')->has('pesanKontak.data', 1)->where('pesanKontak.data.0.id', $target->id)->where('filters.status', 'belum_dibaca'));
    $this->get(route('admin.pesan-kontak.index'))->assertInertia(fn (Assert $page) => $page->has('pesanKontak.data', 15)->where('pesanKontak.total', 18));
});

test('opening detail marks a message as read and admin can toggle its status', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $message = PesanKontak::factory()->create();
    $this->get(route('admin.pesan-kontak.show', $message))->assertInertia(fn (Assert $page) => $page->component('admin/pesan-kontak/show')->where('pesanKontak.id', $message->id));
    expect($message->fresh()->dibaca_pada)->not->toBeNull();
    $this->patch(route('admin.pesan-kontak.toggle-read', $message))->assertSessionHasNoErrors();
    expect($message->fresh()->dibaca_pada)->toBeNull();
});

test('admin can delete a contact message', function (): void {
    $this->actingAs(User::factory()->admin()->create());
    $message = PesanKontak::factory()->create();
    $this->delete(route('admin.pesan-kontak.destroy', $message))->assertRedirect(route('admin.pesan-kontak.index'))->assertInertiaFlash('toast.type', 'success');
    $this->assertModelMissing($message);
});

test('contact message administration is only accessible to admins', function (): void {
    $message = PesanKontak::factory()->create();
    foreach ([['GET', route('admin.pesan-kontak.index')], ['GET', route('admin.pesan-kontak.show', $message)], ['PATCH', route('admin.pesan-kontak.toggle-read', $message)], ['DELETE', route('admin.pesan-kontak.destroy', $message)]] as [$method, $url]) {
        $this->call($method, $url)->assertRedirect(route('login'));
    }
    $this->actingAs(User::factory()->create())->get(route('admin.pesan-kontak.index'))->assertForbidden();
});
