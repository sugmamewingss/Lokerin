<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Lamaran;
use App\Models\LogAkses;
use App\Models\Lowongan;
use App\Models\Penempatan;
use App\Models\ProfilPencari;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PelamarController extends Controller
{
    public function index(Request $request, Lowongan $lowongan)
    {
        $usaha = $request->user()->usaha()->findOrFail($lowongan->usaha_id);
        abort_unless($usaha->status_verifikasi === 'terverifikasi', 403);

        $pelamar = $lowongan->lamaran()->denganPersetujuan()
            ->with(['profil:id,user_id,pendidikan_terakhir,jurusan,persetujuan_data_at', 'profil.user:id,name,no_hp', 'penempatan'])
            ->orderByDesc('skor_cocok')->orderBy('dikirim_at')->orderBy('id')->paginate(15);
        $sisaKuota = max(0, $lowongan->kuota - $lowongan->penempatan()->count());

        return response()->view('usaha.pelamar', compact('lowongan', 'pelamar', 'sisaKuota'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, Lowongan $lowongan, Lamaran $lamaran)
    {
        $usaha = $request->user()->usaha()->findOrFail($lowongan->usaha_id);
        abort_unless($usaha->status_verifikasi === 'terverifikasi', 403);
        abort_unless((int) $lamaran->lowongan_id === $lowongan->id, 404);

        $abaikanPenempatan = Rule::excludeIf($request->input('status') !== 'diterima');
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lamaran::AKSI_USAHA))],
            'tanggal_mulai' => [$abaikanPenempatan, 'required', 'date_format:Y-m-d'],
            'jenis_kontrak' => [$abaikanPenempatan, 'required', Rule::in(array_keys(Penempatan::JENIS_KONTRAK))],
            'terdaftar_bpjs' => [$abaikanPenempatan, 'required', 'boolean'],
        ], [
            'status.in' => 'Pilih tindakan yang tersedia untuk usaha.',
            'tanggal_mulai.required' => 'Isi tanggal mulai kerja.',
            'tanggal_mulai.date_format' => 'Masukkan tanggal mulai kerja yang valid.',
            'jenis_kontrak.required' => 'Pilih jenis kontrak.',
            'jenis_kontrak.in' => 'Pilih jenis kontrak yang tersedia.',
            'terdaftar_bpjs.required' => 'Pilih status kepesertaan BPJS.',
            'terdaftar_bpjs.boolean' => 'Pilih status kepesertaan BPJS yang tersedia.',
        ]);

        $diubah = DB::transaction(function () use ($usaha, $lowongan, $lamaran, $data) {
            // Urutan kunci mengikuti pengiriman lamaran agar proses berjalan konsisten.
            $profil = ProfilPencari::query()->lockForUpdate()->findOrFail($lamaran->profil_id);
            abort_unless($profil->isAktif(), 404);
            $usaha = $usaha->newQuery()->lockForUpdate()->findOrFail($usaha->id);
            abort_unless($usaha->status_verifikasi === 'terverifikasi', 403);

            $lowongan = $usaha->lowongan()->findOrFail($lowongan->id);
            $lamaran = $lowongan->lamaran()->lockForUpdate()->findOrFail($lamaran->id);
            $status = $data['status'];
            if ($lamaran->status === $status) {
                return false;
            }
            if (!$lamaran->bolehPindahKe($status)) {
                throw ValidationException::withMessages(['status' => 'Tindakan ini tidak tersedia pada status lamaran saat ini.']);
            }

            if ($status === 'diterima') {
                if ($lowongan->penempatan()->count() >= $lowongan->kuota) {
                    throw ValidationException::withMessages(['status' => 'Kuota penempatan sudah terpenuhi.']);
                }

                $lamaran->penempatan()->create(Arr::only($data, ['tanggal_mulai', 'jenis_kontrak', 'terdaftar_bpjs']));
            }

            $lamaran->update(['status' => $status]);
            LogAkses::catat('ubah_status_lamaran', "lamaran#{$lamaran->id}:{$status}");

            return true;
        }, 3);

        $pesan = $diubah ? 'Status lamaran diperbarui.' : 'Lamaran sudah memiliki status tersebut.';

        return redirect()->route('usaha.pelamar.index', ['lowongan' => $lowongan, 'page' => max(1, $request->integer('page'))])
            ->with('sukses', $pesan);
    }
}
