<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artikel', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kategori_artikel_id')->constrained('kategori_artikel')->restrictOnDelete();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->text('ringkasan')->nullable();
            $table->longText('konten');
            $table->string('foto_utama')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('unggulan')->default(false);
            $table->dateTime('tanggal_publikasi')->nullable();
            $table->timestamps();
        });
        Schema::create('artikel_destinasi', function (Blueprint $table): void {
            $table->foreignId('artikel_id')->constrained('artikel')->cascadeOnDelete();
            $table->foreignId('destinasi_id')->constrained('destinasi')->restrictOnDelete();
            $table->primary(['artikel_id', 'destinasi_id']);
        });
        Schema::create('artikel_tempat_wisata', function (Blueprint $table): void {
            $table->foreignId('artikel_id')->constrained('artikel')->cascadeOnDelete();
            $table->foreignId('tempat_wisata_id')->constrained('tempat_wisata')->restrictOnDelete();
            $table->primary(['artikel_id', 'tempat_wisata_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikel_tempat_wisata');
        Schema::dropIfExists('artikel_destinasi');
        Schema::dropIfExists('artikel');
    }
};
