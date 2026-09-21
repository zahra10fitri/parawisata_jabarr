<?php

use App\Http\Controllers\Admin\DestinasiController;
use App\Http\Controllers\Admin\PaketPerjalananController as AdminPaketPerjalananController;
use App\Http\Controllers\Admin\TempatWisataController;
use App\Http\Controllers\JelajahiController;
use App\Http\Controllers\PaketPerjalananController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('jelajahi', [JelajahiController::class, 'index'])->name('jelajahi.index');
Route::get('jelajahi/{tempatWisata:slug}', [JelajahiController::class, 'show'])->name('jelajahi.show');
Route::get('paket-perjalanan', [PaketPerjalananController::class, 'index'])->name('paket-perjalanan.index');
Route::get('paket-perjalanan/{paketPerjalanan:slug}', [PaketPerjalananController::class, 'show'])->name('paket-perjalanan.show');

Route::middleware(['auth', 'can:access-admin', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('destinasi', DestinasiController::class)->parameters(['destinasi' => 'destinasi']);
        Route::resource('tempat-wisata', TempatWisataController::class)->parameters(['tempat-wisata' => 'tempat_wisata']);
        Route::resource('paket-perjalanan', AdminPaketPerjalananController::class)->parameters(['paket-perjalanan' => 'paket_perjalanan']);
    });
});

require __DIR__.'/settings.php';
