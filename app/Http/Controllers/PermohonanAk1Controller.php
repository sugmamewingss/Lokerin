<?php

namespace App\Http\Controllers;

use App\Enums\StatusAk1;
use App\Models\PermohonanAk1;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermohonanAk1Controller extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $kader = $this->cekAkses($user);
        $bisaMengajukan = $user->desa?->simpulAk1();
        $permohonan = PermohonanAk1::dapatDilihatOleh($user)
            ->with('pemohon:id,name')->latest()->orderByDesc('id')->paginate(12);

        return response()->view('ak1.index', compact('kader', 'bisaMengajukan', 'permohonan'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $kader = $this->cekAkses($user);
        abort_unless($user->desa?->simpulAk1() && !$user->menunggu_aktivasi, 403);

        $data = $request->validate([
            'nomor_warga_ak1' => $kader ? ['required', 'string', 'max:20'] : ['prohibited'],
            'setuju_ak1' => ['accepted'],
        ], [
            'nomor_warga_ak1.required' => 'Isi nomor HP yang dipakai warga untuk masuk.',
            'setuju_ak1.accepted' => 'Konfirmasi persetujuan untuk mencatat permohonan AK/1.',
        ]);

        $permohonan = DB::transaction(function () use ($user, $kader, $data) {
            // Kunci akun pemohon agar pengajuan mandiri dan pendampingan tidak membuat dua permohonan aktif.
            $akun = User::query()->lockForUpdate()->findOrFail($user->id);
            abort_unless($akun->desa?->simpulAk1() && !$akun->menunggu_aktivasi, 403);

            $pemohon = $kader
                ? User::query()->where('role', User::ROLE_PENCARI)->where('desa_id', $akun->desa_id)
                    ->where('menunggu_aktivasi', false)->where('no_hp', $data['nomor_warga_ak1'])->lockForUpdate()->first()
                : $akun;

            if (!$pemohon) {
                throw ValidationException::withMessages(['nomor_warga_ak1' => 'Akun warga Pakiskembar yang aktif tidak ditemukan. Periksa nomor HP atau dampingi warga menyelesaikan aktivasi.']);
            }

            if (PermohonanAk1::where('pemohon_id', $pemohon->id)->berjalan()->lockForUpdate()->first(['id'])) {
                throw ValidationException::withMessages(['setuju_ak1' => 'Masih ada permohonan aktif. Buka riwayat untuk melanjutkannya.']);
            }

            $permohonan = PermohonanAk1::create([
                'pemohon_id' => $pemohon->id,
                'desa_id' => $akun->desa_id,
                'status' => StatusAk1::Diajukan,
            ]);
            $permohonan->catatStatus();

            return $permohonan;
        }, 3);

        return redirect()->route('ak1.show', $permohonan)->with('sukses', 'Permohonan dicatat di Lokerin.');
    }

    public function show(Request $request, string $permohonan)
    {
        $user = $request->user();
        $kader = $this->cekAkses($user);
        $permohonan = PermohonanAk1::dapatDilihatOleh($user)->with('pemohon:id,name')->findOrFail($permohonan);
        $riwayat = $permohonan->riwayat();

        return response()->view('ak1.detail', compact('kader', 'permohonan', 'riwayat'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, string $permohonan)
    {
        $user = $request->user();
        $kader = $this->cekAkses($user);

        DB::transaction(function () use ($request, $user, $kader, $permohonan) {
            $pengajuan = PermohonanAk1::dapatDilihatOleh($user)->lockForUpdate()->findOrFail($permohonan);
            $data = $request->validate(['status' => ['required', 'string', Rule::enum(StatusAk1::class)]]);
            $status = StatusAk1::from($data['status']);

            if (!in_array($status, $pengajuan->status->langkahBerikutnya($kader), true)) {
                throw ValidationException::withMessages(['status' => 'Tindakan ini tidak tersedia pada tahap sekarang. Muat ulang halaman untuk melihat status terakhir.']);
            }

            $pengajuan->update(['status' => $status]);
            $pengajuan->catatStatus();
        }, 3);

        return redirect()->route('ak1.show', $permohonan)->with('sukses', 'Status permohonan diperbarui.');
    }

    private function cekAkses(User $user): bool
    {
        $kader = $user->hasRole(User::ROLE_KADER);
        abort_if($user->menunggu_aktivasi || ($kader && !$user->desa?->simpulAk1()), 403);

        return $kader;
    }
}
