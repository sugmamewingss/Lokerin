<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Keahlian;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Profil dan keahlian pencari kerja (F-02, F-03). TODO (Rakapaksi): rapikan tampilan chip keahlian. */
class ProfilController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $profil = $user->profilPencari()->firstOrCreate([]);
        $profil->load('keahlian');

        return view('pencari.profil', [
            'user' => $user,
            'profil' => $profil,
            'desa' => Desa::orderBy('nama')->get(),
            'keahlian' => Keahlian::orderBy('kategori')->orderBy('nama')->get()->groupBy('kategori'),
            'jurusanList' => collect(config('lokerin.rumpun_jurusan'))->flatten()->sort()->values(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'desa_id' => ['required', 'exists:desa,id'],
            'tanggal_lahir' => ['nullable', 'date', 'before:-14 years'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'pendidikan_terakhir' => ['required', Rule::in(array_keys(config('lokerin.level_pendidikan')))],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'status_kerja' => ['required', Rule::in(['belum_bekerja', 'paruh_waktu', 'bekerja'])],
            'bersedia_paruh_waktu' => ['boolean'],
            'keahlian' => ['array'],
            'keahlian.*' => ['exists:keahlian,id'],
            'setuju' => ['accepted'],
        ], ['setuju.accepted' => 'Kamu perlu menyetujui penggunaan data agar profil aktif.']);

        $user = $request->user();
        $user->update(['desa_id' => $data['desa_id']]);

        $profil = $user->profilPencari()->firstOrCreate([]);
        $profil->fill(collect($data)->except(['desa_id', 'keahlian', 'setuju'])->all());
        $profil->bersedia_paruh_waktu = $request->boolean('bersedia_paruh_waktu');
        $profil->persetujuan_data_at ??= now();
        $profil->save();
        $profil->keahlian()->sync($data['keahlian'] ?? []);

        return redirect()->route('pencari.beranda')->with('sukses', 'Profil tersimpan.');
    }
}
