<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Controller;
use App\Services\MatchingService;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    /** Beranda rekomendasi (F-07). */
    public function index(Request $request, MatchingService $matching)
    {
        $profil = auth()->user()->profilPencari()->firstOrCreate([]);

        if (!$profil->isAktif()) {
            return redirect()->route('pencari.profil')
                ->with('info', 'Lengkapi profil dan setujui penggunaan data dulu untuk melihat rekomendasi.');
        }

        $sudahDilamar = $profil->lamaran()->pluck('lowongan_id')->all();
        $terdekat = $request->query('urut') === 'jarak';

        return view('pencari.beranda', [
            'profil' => $profil,
            'rekomendasi' => $matching->rekomendasi($profil, terdekat: $terdekat),
            'sudahDilamar' => $sudahDilamar,
            'terdekat' => $terdekat,
        ]);
    }
}
