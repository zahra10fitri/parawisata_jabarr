<?php

namespace App\Actions\Destinasi;

use App\Models\Destinasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GenerateDestinasiSlug
{
    public function handle(string $nama, ?int $exceptId = null): string
    {
        $base = Str::slug($nama) ?: 'destinasi';
        $slug = substr($base, 0, 255);
        $number = 2;

        while (Destinasi::query()->where('slug', $slug)->when($exceptId !== null, fn (Builder $query): Builder => $query->whereKeyNot($exceptId))->exists()) {
            $suffix = '-'.$number++;
            $slug = rtrim(substr($base, 0, 255 - strlen($suffix)), '-').$suffix;
        }

        return $slug;
    }
}
