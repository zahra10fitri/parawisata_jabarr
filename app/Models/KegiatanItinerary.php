<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['itinerary_id', 'judul', 'keterangan', 'waktu', 'urutan'])]
class KegiatanItinerary extends Model
{
    protected $table = 'kegiatan_itinerary';

    protected function casts(): array
    {
        return ['urutan' => 'integer'];
    }

    /** @return BelongsTo<Itinerary, $this> */
    public function itinerary(): BelongsTo
    {
        return $this->belongsTo(Itinerary::class);
    }
}
