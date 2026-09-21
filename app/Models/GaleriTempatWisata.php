<?php

namespace App\Models;

use Database\Factories\GaleriTempatWisataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $tempat_wisata_id
 * @property string $foto
 * @property int $urutan
 */
#[Fillable(['foto', 'urutan'])]
class GaleriTempatWisata extends Model
{
    /** @use HasFactory<GaleriTempatWisataFactory> */
    use HasFactory;

    protected $table = 'galeri_tempat_wisata';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['urutan' => 'integer'];
    }

    /** @return BelongsTo<TempatWisata, $this> */
    public function tempatWisata(): BelongsTo
    {
        return $this->belongsTo(TempatWisata::class, 'tempat_wisata_id');
    }
}
