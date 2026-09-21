<?php

namespace App\Models;

use Database\Factories\PaketPerjalananFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'slug', 'deskripsi', 'foto_utama', 'durasi_hari', 'harga_mulai', 'highlight', 'termasuk', 'tidak_termasuk', 'status', 'unggulan', 'urutan'])]
class PaketPerjalanan extends Model
{
    /** @use HasFactory<PaketPerjalananFactory> */
    use HasFactory;

    protected $table = 'paket_perjalanan';

    protected function casts(): array
    {
        return ['status' => 'boolean', 'unggulan' => 'boolean', 'durasi_hari' => 'integer', 'urutan' => 'integer', 'harga_mulai' => 'decimal:2'];
    }

    /** @return BelongsToMany<Destinasi, $this> */
    public function destinasi(): BelongsToMany
    {
        return $this->belongsToMany(Destinasi::class, 'destinasi_paket_perjalanan');
    }

    /** @return BelongsToMany<TempatWisata, $this> */
    public function tempatWisata(): BelongsToMany
    {
        return $this->belongsToMany(TempatWisata::class, 'paket_perjalanan_tempat_wisata');
    }

    /** @return HasMany<Itinerary, $this> */
    public function itinerary(): HasMany
    {
        return $this->hasMany(Itinerary::class)->orderBy('urutan')->orderBy('hari_ke');
    }
}
