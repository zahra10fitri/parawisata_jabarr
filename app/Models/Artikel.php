<?php

namespace App\Models;

use Database\Factories\ArtikelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['kategori_artikel_id', 'judul', 'slug', 'ringkasan', 'konten', 'foto_utama', 'status', 'unggulan', 'tanggal_publikasi'])]
class Artikel extends Model
{
    /** @use HasFactory<ArtikelFactory> */
    use HasFactory;

    protected $table = 'artikel';

    protected function casts(): array
    {
        return ['status' => 'boolean', 'unggulan' => 'boolean', 'tanggal_publikasi' => 'datetime'];
    }

    /** @return BelongsTo<KategoriArtikel, $this> */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriArtikel::class, 'kategori_artikel_id');
    }

    /** @return BelongsToMany<Destinasi, $this> */
    public function destinasi(): BelongsToMany
    {
        return $this->belongsToMany(Destinasi::class, 'artikel_destinasi');
    }

    /** @return BelongsToMany<TempatWisata, $this> */
    public function tempatWisata(): BelongsToMany
    {
        return $this->belongsToMany(TempatWisata::class, 'artikel_tempat_wisata');
    }
}
