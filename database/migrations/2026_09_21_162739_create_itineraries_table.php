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
        Schema::create('itinerary', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('paket_perjalanan_id')->constrained('paket_perjalanan')->cascadeOnDelete();
            $table->unsignedInteger('hari_ke');
            $table->string('judul')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
            $table->unique(['paket_perjalanan_id', 'hari_ke']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itinerary');
    }
};
