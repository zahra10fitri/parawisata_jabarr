<?php

use App\Http\Controllers\Admin\DestinasiController;
use App\Http\Controllers\Admin\TempatWisataController;
use App\Http\Controllers\JelajahiController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('jelajahi', [JelajahiController::class, 'index'])->name('jelajahi.index');
Route::get('jelajahi/{tempatWisata:slug}', [JelajahiController::class, 'show'])->name('jelajahi.show');

Route::middleware(['auth', 'can:access-admin', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('destinasi', DestinasiController::class)->parameters(['destinasi' => 'destinasi']);
        Route::resource('tempat-wisata', TempatWisataController::class)->parameters(['tempat-wisata' => 'tempat_wisata']);
    });
});

require __DIR__.'/settings.php';
