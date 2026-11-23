<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodeDasborRequest;
use App\Models\Desa;
use App\Models\Usaha;
use App\Services\RekapKetenagakerjaan;

class DasborController extends Controller
{
    public function index(PeriodeDasborRequest $request, RekapKetenagakerjaan $rekap)
    {
        $desa = $request->user()->desa;
        abort_unless($desa, 403, 'Akun kader belum terhubung ke desa.');

        $ringkasan = $rekap->perDesa(Desa::whereKey($desa->id))->sole();
        $ujiCoba = $desa->kecamatan === RekapKetenagakerjaan::KECAMATAN && $desa->nama === RekapKetenagakerjaan::DESA_UJI_COBA
            ? $rekap->ujiCoba($desa, $request->awalUjiCoba())
            : null;

        return view('kader.dasbor', [
            'desa' => $desa,
            'angka' => [
                'Pencari kerja terdaftar' => $ringkasan['pencari'],
                'Lowongan aktif di desa' => $ringkasan['lowongan'],
                'Lamaran bulan ini' => $ringkasan['lamaran_bulan_ini'],
                'Penempatan' => $ringkasan['penempatan'],
            ],
            'perJurusan' => $desa->profilPencari()->disetujui()->selectRaw('jurusan, COUNT(*) as jumlah')
                ->groupBy('jurusan', 'users.desa_id')->orderByDesc('jumlah')->orderBy('jurusan')->get(),
            'menunggu' => Usaha::where('desa_id', $desa->id)->where('status_verifikasi', 'menunggu')->count(),
            'ujiCoba' => $ujiCoba,
        ]);
    }
}
