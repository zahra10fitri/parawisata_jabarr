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
        Schema::create('paket_perjalanan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('foto_utama')->nullable();
            $table->unsignedInteger('durasi_hari');
            $table->decimal('harga_mulai', 12, 2)->nullable();
            $table->text('highlight')->nullable();
            $table->text('termasuk')->nullable();
            $table->text('tidak_termasuk')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('unggulan')->default(false);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
        Schema::create('destinasi_paket_perjalanan', function (Blueprint $table): void {
            $table->foreignId('paket_perjalanan_id')->constrained('paket_perjalanan')->cascadeOnDelete();
            $table->foreignId('destinasi_id')->constrained('destinasi')->restrictOnDelete();
            $table->primary(['paket_perjalanan_id', 'destinasi_id']);
        });
        Schema::create('paket_perjalanan_tempat_wisata', function (Blueprint $table): void {
            $table->foreignId('paket_perjalanan_id')->constrained('paket_perjalanan')->cascadeOnDelete();
            $table->foreignId('tempat_wisata_id')->constrained('tempat_wisata')->restrictOnDelete();
            $table->primary(['paket_perjalanan_id', 'tempat_wisata_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paket_perjalanan_tempat_wisata');
        Schema::dropIfExists('destinasi_paket_perjalanan');
        Schema::dropIfExists('paket_perjalanan');
    }
};
