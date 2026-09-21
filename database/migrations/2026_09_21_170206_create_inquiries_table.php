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
        Schema::create('inquiry', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paket_perjalanan_id')->nullable()->constrained('paket_perjalanan')->nullOnDelete();
            $table->string('nama');
            $table->string('email');
            $table->string('whatsapp', 30);
            $table->date('tanggal_perjalanan');
            $table->unsignedInteger('jumlah_traveler');
            $table->text('catatan')->nullable();
            $table->string('status', 30)->default('BARU');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
        Schema::create('destinasi_inquiry', function (Blueprint $table): void {
            $table->foreignId('inquiry_id')->constrained('inquiry')->cascadeOnDelete();
            $table->foreignId('destinasi_id')->constrained('destinasi')->restrictOnDelete();
            $table->primary(['inquiry_id', 'destinasi_id']);
        });
        Schema::create('inquiry_pengalaman', function (Blueprint $table): void {
            $table->foreignId('inquiry_id')->constrained('inquiry')->cascadeOnDelete();
            $table->foreignId('pengalaman_id')->constrained('pengalaman')->restrictOnDelete();
            $table->primary(['inquiry_id', 'pengalaman_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_pengalaman');
        Schema::dropIfExists('destinasi_inquiry');
        Schema::dropIfExists('inquiry');
    }
};
