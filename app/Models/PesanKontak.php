<?php

namespace App\Models;

use Database\Factories\PesanKontakFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'email', 'whatsapp', 'subjek', 'pesan', 'dibaca_pada'])]
class PesanKontak extends Model
{
    /** @use HasFactory<PesanKontakFactory> */
    use HasFactory;

    protected $table = 'pesan_kontak';

    protected function casts(): array
    {
        return ['dibaca_pada' => 'datetime'];
    }
}
