<?php

namespace App\Models;

use Database\Factories\TempatWisataFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $destinasi_id
 * @property string $nama
 * @property string $slug
 * @property string|null $foto_utama
 * @property bool $status
 * @property bool $unggulan
 * @property int $urutan
 * @property string|null $harga_tiket
 */
#[Fillable(['destinasi_id', 'nama', 'slug', 'deskripsi_singkat', 'deskripsi_lengkap', 'foto_utama', 'lokasi', 'jam_operasional', 'harga_tiket', 'waktu_terbaik', 'tips', 'status', 'unggulan', 'urutan'])]
class TempatWisata extends Model
{
    /** @use HasFactory<TempatWisataFactory> */
    use HasFactory;

    protected $table = 'tempat_wisata';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => 'boolean', 'unggulan' => 'boolean', 'urutan' => 'integer', 'harga_tiket' => 'decimal:2'];
    }

    /** @return BelongsTo<Destinasi, $this> */
    public function destinasi(): BelongsTo
    {
        return $this->belongsTo(Destinasi::class, 'destinasi_id');
    }

    /** @return HasMany<GaleriTempatWisata, $this> */
    public function galeri(): HasMany
    {
        return $this->hasMany(GaleriTempatWisata::class, 'tempat_wisata_id')->orderBy('urutan')->orderBy('id');
    }

    /** @return BelongsToMany<Pengalaman, $this> */
    public function pengalaman(): BelongsToMany
    {
        return $this->belongsToMany(Pengalaman::class, 'pengalaman_tempat_wisata', 'tempat_wisata_id', 'pengalaman_id');
    }

    /** @return BelongsToMany<Aktivitas, $this> */
    public function aktivitas(): BelongsToMany
    {
        return $this->belongsToMany(Aktivitas::class, 'aktivitas_tempat_wisata', 'tempat_wisata_id', 'aktivitas_id');
    }

    /** @return BelongsToMany<Fasilitas, $this> */
    public function fasilitas(): BelongsToMany
    {
        return $this->belongsToMany(Fasilitas::class, 'fasilitas_tempat_wisata', 'tempat_wisata_id', 'fasilitas_id');
    }

    /** @return BelongsToMany<PaketPerjalanan, $this> */
    public function paketPerjalanan(): BelongsToMany
    {
        return $this->belongsToMany(PaketPerjalanan::class, 'paket_perjalanan_tempat_wisata');
    }
}
