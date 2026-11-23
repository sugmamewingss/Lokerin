<?php

namespace App\Http\Controllers\Pencari;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\LogAkses;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
            'desa_id' => ['required', 'integer', 'exists:desa,id'],
            'tanggal_lahir' => ['nullable', 'date', 'before:-14 years'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'pendidikan_terakhir' => ['required', Rule::in(array_keys(config('lokerin.level_pendidikan')))],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'status_kerja' => ['required', Rule::in(['belum_bekerja', 'paruh_waktu', 'bekerja'])],
            'bersedia_paruh_waktu' => ['boolean'],
            'keahlian' => ['array'],
            'keahlian.*' => ['required', 'integer', 'distinct', 'exists:keahlian,id'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $profil = $user->profilPencari()->lockForUpdate()->firstOrNew();
            $persetujuanBaru = !$profil->isAktif();
            if ($persetujuanBaru) {
                $request->validate(['setuju' => ['accepted']], [
                    'setuju.accepted' => 'Setujui penggunaan data sebelum mengaktifkan profil.',
                ]);
                $profil->persetujuan_data_at = now();
            }

            $user->update(['desa_id' => $data['desa_id']]);
            $profil->fill(Arr::except($data, ['desa_id', 'keahlian']));
            $profil->bersedia_paruh_waktu = $request->boolean('bersedia_paruh_waktu');
            $profil->save();
            $profil->keahlian()->sync($data['keahlian'] ?? []);

            if ($persetujuanBaru) {
                LogAkses::catat('persetujuan_data', "profil#{$profil->id}");
            }
        }, 3);

        return redirect()->route('pencari.beranda')->with('sukses', 'Profil tersimpan.');
    }

    public function tarikPersetujuan(Request $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $profil = $user->profilPencari()->lockForUpdate()->first();
            if ($profil?->isAktif()) {
                $profil->update(['persetujuan_data_at' => null]);
                LogAkses::catat('tarik_persetujuan_data', "profil#{$profil->id}");
            }
        }, 3);

        return redirect()->route('pencari.profil')->with('sukses', 'Persetujuan ditarik dan profil dinonaktifkan.');
    }
}
