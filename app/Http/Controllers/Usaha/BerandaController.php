<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;

/**
 * Beranda usaha.
 * TODO (Adefriko): form profil usaha, CRUD lowongan (F-04), daftar pelamar urut skor
 * dan ubah status (F-09). Saat status menjadi "diterima", buat data Penempatan (F-10)
 * dan catat LogAkses::catat('ubah_status_lamaran', "lamaran#{$id}").
 * Pakai Lamaran::bolehPindahKe() untuk validasi urutan status dan
 * Lamaran::kontakTerbuka() sebelum menampilkan nomor HP pelamar.
 */
class BerandaController extends Controller
{
    public function index()
    {
        $usaha = auth()->user()->usaha()->with(['desa', 'lowongan' => fn ($q) => $q->withCount('lamaran')])->first();

        return view('usaha.beranda', compact('usaha'));
    }
}
