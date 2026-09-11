<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin command creates an admin with a hashed password and normalized email', function () {
    $this->artisan('admin:create')
        ->expectsQuestion('Name', 'First Admin')
        ->expectsQuestion('Email', ' Admin@Example.com ')
        ->expectsQuestion('Password', 'StrongPassword123!')
        ->expectsQuestion('Confirm password', 'StrongPassword123!')
        ->expectsOutput('Admin created successfully.')
        ->assertSuccessful();

    $this->assertDatabaseHas('users', [
        'name' => 'First Admin',
        'email' => 'admin@example.com',
        'is_admin' => true,
    ]);
    $user = User::where('email', 'admin@example.com')->firstOrFail();
    expect(Hash::check('StrongPassword123!', $user->password))->toBeTrue();
});

test('admin command does not promote or overwrite an existing user', function () {
    $user = User::factory()->create();
    $originalAttributes = $user->getAttributes();

    $this->artisan('admin:create')
        ->expectsQuestion('Name', 'Another Admin')
        ->expectsQuestion('Email', $user->email)
        ->expectsOutput('The email has already been taken.')
        ->assertFailed();

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('users', $originalAttributes);
});

test('admin command rejects invalid profile data', function (string $name, string $email, string $message) {
    $this->artisan('admin:create')
        ->expectsQuestion('Name', $name)
        ->expectsQuestion('Email', $email)
        ->expectsOutput($message)
        ->assertFailed();

    $this->assertDatabaseCount('users', 0);
})->with([
    'missing name' => ['', 'admin@example.com', 'The name field is required.'],
    'invalid email' => ['Admin', 'invalid', 'The email field must be a valid email address.'],
]);

test('admin command rejects weak or unconfirmed passwords', function (string $password, string $confirmation) {
    $this->artisan('admin:create')
        ->expectsQuestion('Name', 'Admin')
        ->expectsQuestion('Email', 'admin@example.com')
        ->expectsQuestion('Password', $password)
        ->expectsQuestion('Confirm password', $confirmation)
        ->assertFailed();

    $this->assertDatabaseCount('users', 0);
})->with([
    'too short' => ['Short123!', 'Short123!'],
    'no mixed case' => ['lowercase123!', 'lowercase123!'],
    'no numbers' => ['StrongPassword!', 'StrongPassword!'],
    'no symbols' => ['StrongPassword123', 'StrongPassword123'],
    'not confirmed' => ['StrongPassword123!', 'DifferentPassword123!'],
]);

test('admin command refuses noninteractive execution', function () {
    $this->artisan('admin:create', ['--no-interaction' => true])
        ->expectsOutput('Run this command interactively to enter a hidden password.')
        ->assertFailed();

    $this->assertDatabaseCount('users', 0);
});
