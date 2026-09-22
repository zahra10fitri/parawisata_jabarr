<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePesanKontakRequest;
use App\Models\PesanKontak;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class KontakController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('kontak');
    }

    public function store(StorePesanKontakRequest $request): RedirectResponse
    {
        PesanKontak::query()->create($request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pesan Anda berhasil dikirim. Tim Jelajah Jabar akan segera menghubungi Anda.']);

        return to_route('kontak.create');
    }
}
