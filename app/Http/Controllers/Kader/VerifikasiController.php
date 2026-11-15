<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\LogAkses;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Verifikasi usaha oleh kader desa (F-06). Pemilik modul: Ahmad Rosihan Anwar. */
class VerifikasiController extends Controller
{
    public function index()
    {
        $usaha = Usaha::with('user')
            ->where('desa_id', auth()->user()->desa_id)
            ->orderByRaw("case status_verifikasi when 'menunggu' then 0 else 1 end")
            ->latest()->get();

        return view('kader.verifikasi', compact('usaha'));
    }

    public function update(Request $request, Usaha $usaha)
    {
        // Kader hanya boleh memverifikasi usaha di desanya sendiri.
        abort_unless((int) $usaha->desa_id === (int) $request->user()->desa_id, 403);

        $data = $request->validate(['status' => ['required', Rule::in(['terverifikasi', 'ditolak'])]]);

        $usaha->update([
            'status_verifikasi' => $data['status'],
            'diverifikasi_oleh' => $request->user()->id,
            'diverifikasi_at' => now(),
        ]);
        LogAkses::catat('verifikasi_usaha', "usaha#{$usaha->id}:{$data['status']}");

        return back()->with('sukses', "{$usaha->nama_usaha} ditandai {$data['status']}.");
    }
}
