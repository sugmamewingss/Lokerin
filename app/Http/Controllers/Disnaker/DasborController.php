<?php

namespace App\Http\Controllers\Disnaker;

use App\Http\Controllers\Controller;
use App\Http\Requests\PeriodeDasborRequest;
use App\Models\Desa;
use App\Services\RekapKetenagakerjaan;

class DasborController extends Controller
{
    public function index(PeriodeDasborRequest $request, RekapKetenagakerjaan $data)
    {
        $pakis = Desa::where('kecamatan', RekapKetenagakerjaan::KECAMATAN);
        $desaUjiCoba = (clone $pakis)->where('nama', RekapKetenagakerjaan::DESA_UJI_COBA)->first();
        $rekap = $data->perDesa($pakis);
        $ujiCoba = $desaUjiCoba ? $data->ujiCoba($desaUjiCoba, $request->awalUjiCoba()) : null;

        return view('disnaker.dasbor', compact('rekap', 'ujiCoba'));
    }
}
