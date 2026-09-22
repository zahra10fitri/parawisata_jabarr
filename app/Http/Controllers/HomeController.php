<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\Pengalaman;
use App\Models\TempatWisata;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $destinasi = Destinasi::query()->where('status', true)->orderByDesc('unggulan')->orderBy('urutan')->orderBy('id')->limit(5)->get();
        $wisata = TempatWisata::query()->where('status', true)->with('destinasi:id,nama')->orderByDesc('unggulan')->orderBy('urutan')->orderBy('id')->limit(6)->get();

        return Inertia::render('welcome', [
            'heroImage' => $this->url($destinasi->firstWhere('foto_utama', '!=', null)?->foto_utama ?? $wisata->firstWhere('foto_utama', '!=', null)?->foto_utama),
            'destinasi' => $destinasi->map(fn (Destinasi $item): array => [...$item->only(['id', 'nama', 'slug', 'deskripsi_singkat', 'lokasi']), 'foto_url' => $this->url($item->foto_utama)]),
            'pengalaman' => Pengalaman::query()->where('status', true)->orderBy('nama')->limit(8)->get()->map(fn (Pengalaman $item): array => [...$item->only(['id', 'nama', 'slug', 'deskripsi']), 'gambar_url' => $this->url($item->ikon_atau_gambar)]),
            'wisata' => $wisata->map(fn (TempatWisata $item): array => [...$item->only(['id', 'nama', 'slug', 'deskripsi_singkat', 'lokasi', 'harga_tiket']), 'foto_url' => $this->url($item->foto_utama), 'destinasi' => $item->destinasi->only(['id', 'nama'])]),
            'paket' => PaketPerjalanan::query()->where('status', true)->with('destinasi:id,nama')->orderByDesc('unggulan')->orderBy('urutan')->orderBy('id')->limit(4)->get()->map(fn (PaketPerjalanan $item): array => [...$item->only(['id', 'nama', 'slug', 'durasi_hari', 'harga_mulai']), 'foto_url' => $this->url($item->foto_utama), 'destinasi' => $item->destinasi->map->only(['id', 'nama'])->all()]),
            'artikel' => Artikel::query()->where('status', true)->whereNotNull('tanggal_publikasi')->where('tanggal_publikasi', '<=', now())->with('kategori:id,nama')->orderByDesc('tanggal_publikasi')->limit(3)->get()->map(fn (Artikel $item): array => [...$item->only(['id', 'judul', 'slug', 'ringkasan', 'tanggal_publikasi']), 'foto_url' => $this->url($item->foto_utama), 'kategori' => $item->kategori->only(['id', 'nama'])]),
        ]);
    }

    private function url(?string $path): ?string
    {
        return $path === null ? null : Storage::disk('public')->url($path);
    }
}
