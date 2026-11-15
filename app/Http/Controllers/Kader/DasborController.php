<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\ProfilPencari;
use App\Models\Usaha;

/** Dasbor desa (F-11). TODO (Muflih): grafik pencari kerja per jurusan dengan Chart.js. */
class DasborController extends Controller
{
    public function index()
    {
        $desa = auth()->user()->desa;
        abort_unless($desa, 403, 'Akun kader belum terhubung ke desa.');

        $pencariDesa = ProfilPencari::whereHas('user', fn ($q) => $q->where('desa_id', $desa->id));
        $lamaranDesa = Lamaran::whereHas('profil.user', fn ($q) => $q->where('desa_id', $desa->id));

        return view('kader.dasbor', [
            'desa' => $desa,
            'angka' => [
                'Pencari kerja terdaftar' => (clone $pencariDesa)->whereNotNull('persetujuan_data_at')->count(),
                'Lowongan aktif di desa' => Lowongan::tampil()->whereHas('usaha', fn ($q) => $q->where('desa_id', $desa->id))->count(),
                'Lamaran bulan ini' => (clone $lamaranDesa)->where('dikirim_at', '>=', now()->startOfMonth())->count(),
                'Penempatan' => (clone $lamaranDesa)->where('status', 'diterima')->count(),
            ],
            'perJurusan' => (clone $pencariDesa)->selectRaw('jurusan, count(*) as jumlah')->groupBy('jurusan')->orderByDesc('jumlah')->get(),
            'menunggu' => Usaha::where('desa_id', $desa->id)->where('status_verifikasi', 'menunggu')->count(),
        ]);
    }
}
