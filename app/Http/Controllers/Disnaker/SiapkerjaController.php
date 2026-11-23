<?php

namespace App\Http\Controllers\Disnaker;

use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use App\Services\RekapKetenagakerjaan;
use Illuminate\Http\Response;

class SiapkerjaController extends Controller
{
    public function index(): Response
    {
        $lowongan = Lowongan::tampil()
            ->whereHas('usaha.desa', fn ($desa) => $desa->where('kecamatan', RekapKetenagakerjaan::KECAMATAN))
            ->with(['usaha:id,nama_usaha,desa_id', 'usaha.desa:id,nama'])
            ->orderBy('judul')->orderBy('id')
            ->paginate(15, ['id', 'usaha_id', 'judul', 'jenis_kerja', 'pendidikan_min', 'tanggal_tutup']);

        return response()->view('disnaker.siapkerja', compact('lowongan'))->header('Cache-Control', 'private, no-store');
    }
}
