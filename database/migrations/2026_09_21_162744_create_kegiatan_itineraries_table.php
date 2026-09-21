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
        Schema::create('kegiatan_itinerary', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('itinerary_id')->constrained('itinerary')->cascadeOnDelete();
            $table->string('judul');
            $table->text('keterangan')->nullable();
            $table->time('waktu')->nullable();
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_itinerary');
    }
};
