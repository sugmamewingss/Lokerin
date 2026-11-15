<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Controller;
use App\Services\MatchingService;

class BerandaController extends Controller
{
    /** Beranda rekomendasi (F-07). */
    public function index(MatchingService $matching)
    {
        $profil = auth()->user()->profilPencari()->firstOrCreate([]);

        if (!$profil->isAktif()) {
            return redirect()->route('pencari.profil')
                ->with('info', 'Lengkapi profil dan setujui penggunaan data dulu untuk melihat rekomendasi.');
        }

        $sudahDilamar = $profil->lamaran()->pluck('lowongan_id')->all();

        return view('pencari.beranda', [
            'profil' => $profil,
            'rekomendasi' => $matching->rekomendasi($profil),
            'sudahDilamar' => $sudahDilamar,
        ]);
    }
}
