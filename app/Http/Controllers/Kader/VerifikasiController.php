<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\LogAkses;
use App\Models\Usaha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Verifikasi usaha oleh kader desa (F-06). Pemilik modul: Ahmad Rosihan Anwar. */
class VerifikasiController extends Controller
{
    public function index()
    {
        $usaha = Usaha::with('user:id,name')
            ->where('desa_id', auth()->user()->desa_id)
            ->orderByRaw("case status_verifikasi when 'menunggu' then 0 else 1 end")
            ->latest()->get();

        return view('kader.verifikasi', compact('usaha'));
    }

    public function update(Request $request, Usaha $usaha)
    {
        $usaha = DB::transaction(function () use ($request, $usaha) {
            $usaha = Usaha::query()->lockForUpdate()->findOrFail($usaha->id);
            abort_unless((int) $usaha->desa_id === (int) $request->user()->desa_id, 403);
            $data = $request->validate([
                'status' => ['required', Rule::in(['terverifikasi', 'ditolak'])],
                'versi_profil' => ['required', 'string', 'size:64'],
            ]);
            if ($data['versi_profil'] !== $usaha->versiVerifikasi()) {
                throw ValidationException::withMessages(['versi_profil' => 'Profil usaha berubah. Tinjau data terbaru sebelum memverifikasi.']);
            }

            $usaha->update([
                'status_verifikasi' => $data['status'],
                'diverifikasi_oleh' => $request->user()->id,
                'diverifikasi_at' => now(),
            ]);
            LogAkses::catat('verifikasi_usaha', "usaha#{$usaha->id}:{$data['status']}");

            return $usaha;
        }, 3);

        return back()->with('sukses', "{$usaha->nama_usaha} ditandai {$usaha->status_verifikasi}.");
    }
}
