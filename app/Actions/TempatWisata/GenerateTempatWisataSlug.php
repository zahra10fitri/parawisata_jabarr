<?php

namespace App\Actions\TempatWisata;

use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class GenerateTempatWisataSlug
{
    public function handle(string $nama, ?int $exceptId = null): string
    {
        $base = Str::slug($nama) ?: 'tempat-wisata';
        $slug = substr($base, 0, 255);
        $number = 2;

        while (TempatWisata::query()->where('slug', $slug)->when($exceptId !== null, fn (Builder $query): Builder => $query->whereKeyNot($exceptId))->exists()) {
            $suffix = '-'.$number++;
            $slug = rtrim(substr($base, 0, 255 - strlen($suffix)), '-').$suffix;
        }

        return $slug;
    }
}
