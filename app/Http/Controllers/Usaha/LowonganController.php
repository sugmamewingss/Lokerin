<?php

namespace App\Http\Controllers\Usaha;

use App\Http\Controllers\Controller;
use App\Models\Keahlian;
use App\Models\Lowongan;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LowonganController extends Controller
{
    public function create(Request $request)
    {
        $usaha = $request->user()->usaha()->first();
        if (!$usaha) {
            return redirect()->route('usaha.profil')->with('info', 'Lengkapi profil usaha untuk membuat lowongan.');
        }

        return $this->form(new Lowongan(['status' => 'draf', 'jenis_kerja' => 'penuh_waktu', 'kuota' => 1]), $usaha->status_verifikasi);
    }

    public function store(Request $request)
    {
        return $this->simpan($request);
    }

    public function edit(Request $request, Lowongan $lowongan)
    {
        $usaha = $request->user()->usaha()->findOrFail($lowongan->usaha_id);

        return $this->form($lowongan->load('keahlian'), $usaha->status_verifikasi);
    }

    public function update(Request $request, Lowongan $lowongan)
    {
        $request->user()->usaha()->findOrFail($lowongan->usaha_id);

        return $this->simpan($request, $lowongan);
    }

    private function form(Lowongan $lowongan, string $statusVerifikasi)
    {
        return view('usaha.lowongan', [
            'lowongan' => $lowongan,
            'bolehTerbit' => $statusVerifikasi === 'terverifikasi',
            'keahlian' => Keahlian::orderBy('kategori')->orderBy('nama')->get()->groupBy('kategori'),
            'jurusan' => collect(config('lokerin.rumpun_jurusan'))->flatten()->sort(),
        ]);
    }

    private function simpan(Request $request, ?Lowongan $lowongan = null)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required_if:status,aktif', 'nullable', 'string', 'max:10000'],
            'jenis_kerja' => ['required', Rule::in(array_keys(Lowongan::JENIS_KERJA))],
            'pendidikan_min' => ['required', Rule::in(array_keys(config('lokerin.level_pendidikan')))],
            'jurusan_target' => ['nullable', 'string', 'max:100'],
            'kuota' => ['required', 'integer', 'between:1,65535'],
            'gaji_min' => ['nullable', 'integer', 'between:0,4294967295'],
            'gaji_max' => ['nullable', 'integer', 'between:0,4294967295', Rule::when($request->filled('gaji_min'), 'gte:gaji_min')],
            'tanggal_tutup' => ['nullable', 'date_format:Y-m-d', Rule::when($request->input('status') === 'aktif', 'after_or_equal:'.Lowongan::tanggalDiPakis())],
            'status' => ['required', Rule::in(array_keys(Lowongan::STATUS))],
            'keahlian' => ['array:'.Keahlian::pluck('id')->implode(',')],
            'keahlian.*' => ['nullable', Rule::in(['wajib', 'tambahan'])],
        ]);

        DB::transaction(function () use ($request, $lowongan, $data) {
            $usaha = $request->user()->usaha()->lockForUpdate()->firstOrFail();
            if ($data['status'] === 'aktif' && $usaha->status_verifikasi !== 'terverifikasi') {
                throw ValidationException::withMessages(['status' => 'Lowongan baru bisa diterbitkan setelah usaha diverifikasi.']);
            }

            $lowongan = $lowongan
                ? $usaha->lowongan()->findOrFail($lowongan->id)
                : $usaha->lowongan()->make();
            if ($lowongan->exists && $data['kuota'] < $lowongan->penempatan()->count()) {
                throw ValidationException::withMessages(['kuota' => 'Kuota minimal sama dengan jumlah penempatan yang sudah tercatat.']);
            }

            $lowongan->fill(Arr::except($data, ['keahlian']));
            $lowongan->catatPenerbitanPertama();
            $lowongan->save();
            $keahlian = collect($data['keahlian'] ?? [])->filter()
                ->map(fn (string $jenis) => ['wajib' => $jenis === 'wajib']);
            $lowongan->keahlian()->sync($keahlian->all());
        }, 3);

        return redirect()->route('usaha.beranda')->with('sukses', 'Lowongan tersimpan.');
    }
}
