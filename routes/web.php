<?php

use App\Http\Controllers\Admin\DestinasiController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'can:access-admin', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('destinasi', DestinasiController::class)->parameters(['destinasi' => 'destinasi']);
    });
});

require __DIR__.'/settings.php';
