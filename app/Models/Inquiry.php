<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['paket_perjalanan_id', 'nama', 'email', 'whatsapp', 'tanggal_perjalanan', 'jumlah_traveler', 'catatan', 'status'])]
class Inquiry extends Model
{
    public const STATUS_BARU = 'BARU';

    public const STATUSES = ['BARU', 'DIPROSES', 'PENAWARAN DIKIRIM', 'MENUNGGU PEMBAYARAN', 'PEMBAYARAN DIVERIFIKASI', 'PERJALANAN DIKONFIRMASI', 'SELESAI', 'DIBATALKAN', 'DITOLAK'];

    protected $table = 'inquiry';

    protected function casts(): array
    {
        return ['tanggal_perjalanan' => 'date', 'jumlah_traveler' => 'integer'];
    }

    /** @return BelongsTo<PaketPerjalanan, $this> */
    public function paketPerjalanan(): BelongsTo
    {
        return $this->belongsTo(PaketPerjalanan::class);
    }

    /** @return BelongsToMany<Destinasi, $this> */
    public function destinasi(): BelongsToMany
    {
        return $this->belongsToMany(Destinasi::class, 'destinasi_inquiry');
    }

    /** @return BelongsToMany<Pengalaman, $this> */
    public function pengalaman(): BelongsToMany
    {
        return $this->belongsToMany(Pengalaman::class, 'inquiry_pengalaman');
    }
}
