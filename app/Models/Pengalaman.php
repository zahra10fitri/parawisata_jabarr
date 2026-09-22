<?php

namespace App\Models;

use Database\Factories\PengalamanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property string $slug
 * @property string|null $deskripsi
 * @property string|null $ikon_atau_gambar
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nama', 'slug', 'deskripsi', 'ikon_atau_gambar', 'status'])]
class Pengalaman extends Model
{
    /** @use HasFactory<PengalamanFactory> */
    use HasFactory;

    protected $table = 'pengalaman';

    /** @return BelongsToMany<Inquiry, $this> */
    public function inquiry(): BelongsToMany
    {
        return $this->belongsToMany(Inquiry::class, 'inquiry_pengalaman');
    }

    /** @return BelongsToMany<TempatWisata, $this> */
    public function tempatWisata(): BelongsToMany
    {
        return $this->belongsToMany(TempatWisata::class, 'pengalaman_tempat_wisata', 'pengalaman_id', 'tempat_wisata_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
