<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinasi', function (Blueprint $table): void {
            $table->id();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi_singkat')->nullable();
            $table->longText('deskripsi_lengkap')->nullable();
            $table->string('foto_utama')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('waktu_terbaik')->nullable();
            $table->text('cara_menuju')->nullable();
            $table->text('transportasi_lokal')->nullable();
            $table->text('tips')->nullable();
            $table->boolean('status')->default(false);
            $table->boolean('unggulan')->default(false);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinasi');
    }
};
