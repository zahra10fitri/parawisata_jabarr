<?php

namespace App\Models;

use Database\Factories\KategoriArtikelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'slug'])]
class KategoriArtikel extends Model
{
    /** @use HasFactory<KategoriArtikelFactory> */
    use HasFactory;

    protected $table = 'kategori_artikel';

    /** @return HasMany<Artikel, $this> */
    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class);
    }
}
