<?php

use App\Http\Controllers\Admin\DestinasiController;
use App\Http\Controllers\Admin\ItineraryController;
use App\Http\Controllers\Admin\PaketPerjalananController as AdminPaketPerjalananController;
use App\Http\Controllers\Admin\TempatWisataController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\JelajahiController;
use App\Http\Controllers\PaketPerjalananController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('jelajahi', [JelajahiController::class, 'index'])->name('jelajahi.index');
Route::get('jelajahi/{tempatWisata:slug}', [JelajahiController::class, 'show'])->name('jelajahi.show');
Route::get('paket-perjalanan', [PaketPerjalananController::class, 'index'])->name('paket-perjalanan.index');
Route::get('paket-perjalanan/{paketPerjalanan:slug}', [PaketPerjalananController::class, 'show'])->name('paket-perjalanan.show');
Route::get('rencanakan-perjalanan', [InquiryController::class, 'create'])->name('inquiry.create');
Route::post('rencanakan-perjalanan', [InquiryController::class, 'store'])->name('inquiry.store');

Route::middleware(['auth', 'can:access-admin', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('destinasi', DestinasiController::class)->parameters(['destinasi' => 'destinasi']);
        Route::resource('tempat-wisata', TempatWisataController::class)->parameters(['tempat-wisata' => 'tempat_wisata']);
        Route::resource('paket-perjalanan', AdminPaketPerjalananController::class)->parameters(['paket-perjalanan' => 'paket_perjalanan']);
        Route::prefix('paket-perjalanan/{paket_perjalanan}/itinerary')->name('paket-perjalanan.itinerary.')->group(function (): void {
            Route::get('/', [ItineraryController::class, 'index'])->name('index');
            Route::post('/', [ItineraryController::class, 'store'])->name('store');
            Route::put('{itinerary}', [ItineraryController::class, 'update'])->name('update');
            Route::delete('{itinerary}', [ItineraryController::class, 'destroy'])->name('destroy');
            Route::put('urutkan/hari', [ItineraryController::class, 'reorder'])->name('reorder');
            Route::post('{itinerary}/kegiatan', [ItineraryController::class, 'storeKegiatan'])->name('kegiatan.store');
            Route::put('{itinerary}/kegiatan/{kegiatan}', [ItineraryController::class, 'updateKegiatan'])->name('kegiatan.update');
            Route::delete('{itinerary}/kegiatan/{kegiatan}', [ItineraryController::class, 'destroyKegiatan'])->name('kegiatan.destroy');
            Route::put('{itinerary}/kegiatan-urutkan', [ItineraryController::class, 'reorderKegiatan'])->name('kegiatan.reorder');
        });
    });
});

require __DIR__.'/settings.php';
