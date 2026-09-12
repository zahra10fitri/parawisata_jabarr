<?php

namespace App\Models;

use Database\Factories\DestinasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property string $slug
 * @property string|null $deskripsi_singkat
 * @property string|null $deskripsi_lengkap
 * @property string|null $foto_utama
 * @property string|null $lokasi
 * @property string|null $waktu_terbaik
 * @property string|null $cara_menuju
 * @property string|null $transportasi_lokal
 * @property string|null $tips
 * @property bool $status
 * @property bool $unggulan
 * @property int $urutan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['nama', 'slug', 'deskripsi_singkat', 'deskripsi_lengkap', 'foto_utama', 'lokasi', 'waktu_terbaik', 'cara_menuju', 'transportasi_lokal', 'tips', 'status', 'unggulan', 'urutan'])]
class Destinasi extends Model
{
    /** @use HasFactory<DestinasiFactory> */
    use HasFactory;

    protected $table = 'destinasi';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'unggulan' => 'boolean',
            'urutan' => 'integer',
        ];
    }
}
