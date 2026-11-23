<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfilController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        $usaha = $user->usaha()->first() ?? new Usaha(['desa_id' => $user->desa_id]);

        return view('usaha.profil', ['usaha' => $usaha, 'desa' => Desa::orderBy('nama')->get()]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_usaha' => ['required', 'string', 'max:150'],
            'jenis_usaha' => ['required', 'string', 'max:100'],
            'is_umkm' => ['nullable', 'boolean'],
            'desa_id' => ['required', 'integer', 'exists:desa,id'],
            'alamat' => ['required', 'string', 'max:255'],
        ]);

        $menunggu = DB::transaction(function () use ($request, $data) {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $usaha = $user->usaha()->lockForUpdate()->firstOrNew();
            $usaha->fill($data);

            if ($usaha->isDirty()) {
                $usaha->status_verifikasi = 'menunggu';
                $usaha->diverifikasi_oleh = null;
                $usaha->diverifikasi_at = null;
                $usaha->save();
            }

            return $usaha->status_verifikasi === 'menunggu';
        }, 3);

        return redirect()->route('usaha.beranda')->with('sukses', $menunggu
            ? 'Profil tersimpan. Tunggu verifikasi dari kader di desa usaha.'
            : 'Profil usaha tersimpan.');
    }
}
