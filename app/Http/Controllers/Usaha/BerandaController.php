<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;

class BerandaController extends Controller
{
    public function index()
    {
        $usaha = auth()->user()->usaha()
            ->with(['desa', 'lowongan' => fn ($q) => $q->withCount(['lamaran' => fn ($l) => $l->denganPersetujuan(), 'penempatan'])->latest()])
            ->first();

        return view('usaha.beranda', compact('usaha'));
    }
}
