<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Lowongan;
use App\Models\User;
use App\Services\MatchingService;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    public function beranda()
    {
        return view('publik.beranda', [
            'jumlahLowongan' => Lowongan::tampil()->count(),
            'terbaru' => Lowongan::tampil()->with('usaha.desa')->latest()->take(6)->get(),
        ]);
    }

    /** Daftar lowongan publik dengan filter desa dan jenis kerja (F-14). */
    public function lowongan(Request $request)
    {
        $lowongan = Lowongan::tampil()
            ->with('usaha.desa')
            ->when($request->desa, fn ($q, $d) => $q->whereHas('usaha', fn ($u) => $u->where('desa_id', $d)))
            ->when($request->jenis, fn ($q, $j) => $q->where('jenis_kerja', $j))
            ->latest()->paginate(12)->withQueryString();

        return view('publik.lowongan', ['lowongan' => $lowongan, 'desa' => Desa::orderBy('nama')->get()]);
    }

    public function detail(Lowongan $lowongan, MatchingService $matching)
    {
        abort_unless(Lowongan::tampil()->whereKey($lowongan->id)->exists(), 404);
        $lowongan->load(['usaha.desa', 'keahlian']);

        $hasil = null;
        $user = auth()->user();
        $profil = $user?->hasRole(User::ROLE_PENCARI) ? $user->profilPencari()->first() : null;
        if ($profil && $profil->isAktif()) {
            $hasil = $matching->hitung($profil, $lowongan);
        }
        $pelatihan = $matching->saranPelatihan($hasil['keahlian_kurang'] ?? $lowongan->keahlianWajib()->pluck('keahlian.id')->all());

        return view('publik.detail', compact('lowongan', 'hasil', 'pelatihan', 'profil'));
    }
}
