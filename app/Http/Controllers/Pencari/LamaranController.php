<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use App\Services\MatchingService;

/** Lamaran pencari kerja (F-08). */
class LamaranController extends Controller
{
    public function index()
    {
        $profil = auth()->user()->profilPencari;
        $lamaran = $profil
            ? $profil->lamaran()->with('lowongan.usaha.desa')->latest('dikirim_at')->get()
            : collect();

        return view('pencari.lamaran', compact('lamaran'));
    }

    public function store(Lowongan $lowongan, MatchingService $matching)
    {
        $profil = auth()->user()->profilPencari;
        abort_unless($profil && $profil->isAktif(), 403, 'Lengkapi profil terlebih dahulu.');
        abort_unless(Lowongan::tampil()->whereKey($lowongan->id)->exists(), 404);

        $lamaran = $profil->lamaran()->firstOrCreate(
            ['lowongan_id' => $lowongan->id],
            ['skor_cocok' => $matching->hitung($profil, $lowongan)['skor'], 'status' => 'dikirim', 'dikirim_at' => now()]
        );

        $pesan = $lamaran->wasRecentlyCreated ? 'Lamaran terkirim.' : 'Kamu sudah melamar lowongan ini.';

        return redirect()->route('pencari.lamaran')->with('sukses', $pesan);
    }
}
