<?php

namespace App\Http\Controllers;

use App\Models\Destinasi;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TentangController extends Controller
{
    public function __invoke(): Response
    {
        $foto = Destinasi::query()->where('status', true)->whereNotNull('foto_utama')->orderByDesc('unggulan')->orderBy('urutan')->value('foto_utama');

        return Inertia::render('tentang', ['heroImage' => $foto === null ? null : Storage::disk('public')->url($foto)]);
    }
}
