<?php

namespace App\Services;

use App\Models\Lowongan;
use App\Models\Pelatihan;
use App\Models\ProfilPencari;
use Illuminate\Support\Collection;

/**
 * Penghubung antara model database dan SkorKecocokan.
 * Pemilik modul (Rancangan Bab 8.2): Alfi.
 */
class MatchingService
{
    private SkorKecocokan $skor;

    public function __construct()
    {
        $this->skor = new SkorKecocokan(config('lokerin'));
    }

    public function hitung(ProfilPencari $profil, Lowongan $lowongan): array
    {
        return $this->skor->hitung($this->dataProfil($profil), $this->dataLowongan($lowongan));
    }

    /**
     * Rekomendasi lowongan untuk satu pencari kerja (F-07).
     * Mengembalikan koleksi ['lowongan' => Lowongan, 'hasil' => array, 'pelatihan' => Collection], urut skor menurun.
     */
    public function rekomendasi(ProfilPencari $profil, ?int $ambang = null): Collection
    {
        $ambang ??= config('lokerin.ambang_rekomendasi');
        $dataProfil = $this->dataProfil($profil);

        return Lowongan::tampil()
            ->with(['usaha.desa', 'keahlian'])
            ->get()
            ->map(function (Lowongan $l) use ($dataProfil) {
                $hasil = $this->skor->hitung($dataProfil, $this->dataLowongan($l));

                return [
                    'lowongan' => $l,
                    'hasil' => $hasil,
                    'pelatihan' => $this->saranPelatihan($hasil['keahlian_kurang']),
                ];
            })
            ->filter(fn ($r) => $r['hasil']['skor'] >= $ambang)
            ->sortByDesc(fn ($r) => $r['hasil']['skor'])
            ->values();
    }

    /** Saran pelatihan untuk keahlian yang belum dimiliki (F-12). */
    public function saranPelatihan(array $keahlianIds): Collection
    {
        if ($keahlianIds === []) {
            return collect();
        }

        return Pelatihan::with('keahlian')->whereIn('keahlian_id', $keahlianIds)->get();
    }

    private function dataProfil(ProfilPencari $p): array
    {
        $p->loadMissing(['user.desa', 'keahlian']);

        return [
            'jurusan' => $p->jurusan,
            'keahlian_ids' => $p->keahlian->pluck('id')->all(),
            'desa_id' => $p->user?->desa_id,
            'kecamatan' => $p->user?->desa?->kecamatan,
            'pendidikan' => $p->pendidikan_terakhir,
        ];
    }

    private function dataLowongan(Lowongan $l): array
    {
        $l->loadMissing(['usaha.desa', 'keahlian']);

        return [
            'jurusan_target' => $l->jurusan_target,
            'keahlian_wajib_ids' => $l->keahlian->filter(fn ($k) => (bool) $k->pivot->wajib)->pluck('id')->all(),
            'desa_id' => $l->usaha?->desa_id,
            'kecamatan' => $l->usaha?->desa?->kecamatan,
            'pendidikan_min' => $l->pendidikan_min,
        ];
    }
}
