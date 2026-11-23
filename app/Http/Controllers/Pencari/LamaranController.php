<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\LogAkses;
use App\Models\Lowongan;
use App\Models\Usaha;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Lamaran pencari kerja (F-08). */
class LamaranController extends Controller
{
    public function index()
    {
        $profil = auth()->user()->profilPencari;
        $lamaran = $profil
            ? $profil->lamaran()->with(['lowongan.usaha.desa', 'penempatan'])->latest('dikirim_at')->get()
            : collect();

        return view('pencari.lamaran', compact('lamaran'));
    }

    public function store(Lowongan $lowongan, MatchingService $matching)
    {
        $lamaran = DB::transaction(function () use ($lowongan, $matching) {
            $profil = auth()->user()->profilPencari()->lockForUpdate()->first();
            abort_unless($profil?->isAktif(), 403, 'Lengkapi profil dan setujui penggunaan data terlebih dahulu.');

            // Perubahan lowongan dan verifikasi juga mengunci usaha ini.
            Usaha::query()->lockForUpdate()->findOrFail($lowongan->usaha_id);
            $lowongan = Lowongan::tampil()->findOrFail($lowongan->id);

            return $profil->lamaran()->firstOrCreate(
                ['lowongan_id' => $lowongan->id],
                ['skor_cocok' => $matching->hitung($profil, $lowongan)['skor'], 'status' => 'dikirim', 'dikirim_at' => now()]
            );
        }, 3);

        $pesan = $lamaran->wasRecentlyCreated ? 'Lamaran terkirim.' : 'Kamu sudah melamar lowongan ini.';

        return redirect()->route('pencari.lamaran')->with('sukses', $pesan);
    }

    public function batalkan(Request $request, Lamaran $lamaran)
    {
        DB::transaction(function () use ($request, $lamaran) {
            $profil = $request->user()->profilPencari()->firstOrFail();
            $lamaran = $profil->lamaran()->lockForUpdate()->findOrFail($lamaran->id);
            if ($lamaran->status === 'dibatalkan') {
                return;
            }
            if (!$lamaran->bolehPindahKe('dibatalkan')) {
                throw ValidationException::withMessages(['status' => 'Pembatalan hanya tersedia sebelum tahap wawancara.']);
            }

            $lamaran->update(['status' => 'dibatalkan']);
            LogAkses::catat('ubah_status_lamaran', "lamaran#{$lamaran->id}:dibatalkan");
        }, 3);

        return redirect()->route('pencari.lamaran')->with('sukses', 'Lamaran dibatalkan.');
    }
}
