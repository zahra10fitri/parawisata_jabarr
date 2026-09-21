<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['paket_perjalanan_id', 'hari_ke', 'judul', 'urutan'])]
class Itinerary extends Model
{
    protected $table = 'itinerary';

    protected function casts(): array
    {
        return ['hari_ke' => 'integer', 'urutan' => 'integer'];
    }

    /** @return BelongsTo<PaketPerjalanan, $this> */
    public function paketPerjalanan(): BelongsTo
    {
        return $this->belongsTo(PaketPerjalanan::class);
    }

    /** @return HasMany<KegiatanItinerary, $this> */
    public function kegiatan(): HasMany
    {
        return $this->hasMany(KegiatanItinerary::class)->orderBy('urutan')->orderBy('id');
    }
}
