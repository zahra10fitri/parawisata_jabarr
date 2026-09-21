<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tempat_wisata', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('destinasi_id')->constrained('destinasi')->restrictOnDelete();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi_singkat')->nullable();
            $table->longText('deskripsi_lengkap')->nullable();
            $table->string('foto_utama')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('jam_operasional')->nullable();
            $table->decimal('harga_tiket', 12, 2)->nullable();
            $table->text('waktu_terbaik')->nullable();
            $table->text('tips')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('unggulan')->default(false);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('galeri_tempat_wisata', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tempat_wisata_id')->constrained('tempat_wisata')->cascadeOnDelete();
            $table->string('foto');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });

        foreach (['pengalaman', 'aktivitas', 'fasilitas'] as $master) {
            Schema::create($master.'_tempat_wisata', function (Blueprint $table) use ($master): void {
                $table->foreignId('tempat_wisata_id')->constrained('tempat_wisata')->cascadeOnDelete();
                $table->foreignId($master.'_id')->constrained($master)->cascadeOnDelete();
                $table->primary(['tempat_wisata_id', $master.'_id']);
            });
        }
    }

    public function down(): void
    {
        foreach (['fasilitas', 'aktivitas', 'pengalaman'] as $master) {
            Schema::dropIfExists($master.'_tempat_wisata');
        }
        Schema::dropIfExists('galeri_tempat_wisata');
        Schema::dropIfExists('tempat_wisata');
    }
};
