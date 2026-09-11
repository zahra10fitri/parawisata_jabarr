<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

dataset('admin endpoints', [
    'dashboard' => ['GET', '/dashboard'],
    'settings redirect' => ['GET', '/settings'],
    'profile page' => ['GET', '/settings/profile'],
    'profile update' => ['PATCH', '/settings/profile'],
    'profile deletion' => ['DELETE', '/settings/profile'],
    'security page' => ['GET', '/settings/security'],
    'password update' => ['PUT', '/settings/password'],
    'appearance page' => ['GET', '/settings/appearance'],
]);

test('ordinary users cannot log in even with the correct password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'is_admin' => true,
        'remember' => true,
    ]);

    $response->assertSessionHasErrors(['email' => trans('auth.failed')]);
    $this->assertGuest();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'is_admin' => false]);
});

test('guests must log in before accessing admin endpoints', function (string $method, string $uri) {
    $this->call($method, $uri)->assertRedirect(route('login'));
})->with('admin endpoints');

test('ordinary users cannot access admin endpoints or modify their accounts', function (string $method, string $uri) {
    $user = User::factory()->create();
    $originalAttributes = $user->getAttributes();

    $this->actingAs($user)->call($method, $uri, [
        'name' => 'Changed name',
        'email' => 'changed@example.com',
        'password' => 'password',
        'current_password' => 'password',
        'password_confirmation' => 'password',
        'is_admin' => true,
    ])->assertForbidden();

    $this->assertDatabaseHas('users', $originalAttributes);
})->with('admin endpoints');

test('revoking admin rights blocks an existing login session', function (string $method, string $uri) {
    $user = User::factory()->admin()->create();
    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));
    User::whereKey($user->id)->update(['is_admin' => false]);
    Auth::forgetGuards();

    $this->call($method, $uri)->assertForbidden();
})->with('admin endpoints');

test('admin access gate grants access only to admins', function (bool $isAdmin) {
    $user = User::factory()->make(['is_admin' => $isAdmin]);

    expect(Gate::forUser($user)->allows('access-admin'))->toBe($isAdmin);
})->with(['admin' => true, 'ordinary user' => false]);

test('profile updates cannot change admin status', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Updated Admin',
        'email' => $user->email,
        'is_admin' => false,
    ])->assertSessionHasNoErrors()->assertRedirect(route('profile.edit'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Admin',
        'is_admin' => true,
    ]);
});

test('public registration is unavailable', function (string $method) {
    $this->call($method, '/register', [
        'name' => 'Unauthorized Admin',
        'email' => 'unauthorized@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
        'is_admin' => true,
    ])->assertNotFound();

    $this->assertDatabaseCount('users', 0);
})->with(['registration page' => 'GET', 'registration submission' => 'POST']);

test('a user with revoked admin rights can still log out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest();
});

test('admins can open settings pages', function (string $routeName) {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->get(route($routeName))->assertOk();
})->with(['security.edit', 'appearance.edit']);
