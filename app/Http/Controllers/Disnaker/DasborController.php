<?php

namespace App\Http\Controllers\Disnaker;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\ProfilPencari;

/** Dasbor kecamatan (F-11). TODO (Muflih): grafik dan ekspor CSV (F-17). */
class DasborController extends Controller
{
    public function index()
    {
        $rekap = Desa::orderBy('nama')->get()->map(fn (Desa $d) => [
            'desa' => $d->nama,
            'pencari' => ProfilPencari::whereNotNull('persetujuan_data_at')->whereHas('user', fn ($q) => $q->where('desa_id', $d->id))->count(),
            'lowongan' => Lowongan::tampil()->whereHas('usaha', fn ($q) => $q->where('desa_id', $d->id))->count(),
            'lamaran' => Lamaran::whereHas('profil.user', fn ($q) => $q->where('desa_id', $d->id))->count(),
            'penempatan' => Lamaran::where('status', 'diterima')->whereHas('profil.user', fn ($q) => $q->where('desa_id', $d->id))->count(),
        ]);

        return view('disnaker.dasbor', compact('rekap'));
    }
}
