<?php

namespace App\Http\Controllers;

use App\Http\Requests\GlobalSearchRequest;
use App\Models\Artikel;
use App\Models\Destinasi;
use App\Models\PaketPerjalanan;
use App\Models\TempatWisata;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class GlobalSearchController extends Controller
{
    public function index(GlobalSearchRequest $request): Response
    {
        $query = $request->validated('q') ?? '';
        $term = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query);

        return Inertia::render('pencarian/index', [
            'query' => $query,
            'results' => $query === '' ? $this->emptyResults() : [
                'destinasi' => Destinasi::query()->where('status', true)->where(fn (Builder $builder) => $this->match($builder, $term, ['nama', 'deskripsi_singkat', 'deskripsi_lengkap', 'lokasi']))->orderByDesc('unggulan')->orderBy('urutan')->limit(12)->get()->map(fn (Destinasi $item): array => $this->result('Destinasi', $item->nama, $item->deskripsi_singkat ?? $item->lokasi, $item->foto_utama, '/destinasi/'.$item->slug)),
                'wisata' => TempatWisata::query()->where('status', true)->where(fn (Builder $builder) => $this->match($builder, $term, ['nama', 'deskripsi_singkat', 'deskripsi_lengkap', 'lokasi']))->orderByDesc('unggulan')->orderBy('urutan')->limit(12)->get()->map(fn (TempatWisata $item): array => $this->result('Tempat Wisata', $item->nama, $item->deskripsi_singkat ?? $item->lokasi, $item->foto_utama, '/jelajahi/'.$item->slug)),
                'paket' => PaketPerjalanan::query()->where('status', true)->where(fn (Builder $builder) => $this->match($builder, $term, ['nama', 'deskripsi', 'highlight']))->orderByDesc('unggulan')->orderBy('urutan')->limit(12)->get()->map(fn (PaketPerjalanan $item): array => $this->result('Paket Perjalanan', $item->nama, $item->deskripsi ?? $item->highlight, $item->foto_utama, '/paket-perjalanan/'.$item->slug)),
                'artikel' => Artikel::query()->where('status', true)->whereNotNull('tanggal_publikasi')->where('tanggal_publikasi', '<=', now())->where(fn (Builder $builder) => $this->match($builder, $term, ['judul', 'ringkasan', 'konten']))->latest('tanggal_publikasi')->limit(12)->get()->map(fn (Artikel $item): array => $this->result('Inspirasi', $item->judul, $item->ringkasan, $item->foto_utama, '/inspirasi/'.$item->slug)),
            ],
        ]);
    }

    /** @param array<int, string> $columns */
    private function match(Builder $query, string $term, array $columns): void
    {
        foreach ($columns as $index => $column) {
            $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
            $query->{$method}("{$column} LIKE ? ESCAPE '!'", ['%'.$term.'%']);
        }
    }

    /** @return array{type: string, title: string, summary: ?string, image_url: ?string, url: string} */
    private function result(string $type, string $title, ?string $summary, ?string $image, string $url): array
    {
        return ['type' => $type, 'title' => $title, 'summary' => $summary, 'image_url' => $image ? Storage::disk('public')->url($image) : null, 'url' => $url];
    }

    /** @return array{destinasi: array<never>, wisata: array<never>, paket: array<never>, artikel: array<never>} */
    private function emptyResults(): array
    {
        return ['destinasi' => [], 'wisata' => [], 'paket' => [], 'artikel' => []];
    }
}
