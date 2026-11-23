<?php

namespace App\Services;

use App\Models\Lowongan;
use App\Models\Pelatihan;
use App\Models\ProfilPencari;
use Illuminate\Support\Arr;
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
     * Urut berdasarkan skor atau jarak. Jarak tidak memengaruhi skor yang disimpan saat melamar.
     */
    public function rekomendasi(ProfilPencari $profil, ?int $ambang = null, bool $terdekat = false): Collection
    {
        $ambang ??= config('lokerin.ambang_rekomendasi');
        $dataProfil = $this->dataProfil($profil);
        $urutSkor = ['hasil.skor', 'desc'];
        $urutJarak = fn (array $a, array $b) => ($a['hasil']['jarak_km'] ?? INF) <=> ($b['hasil']['jarak_km'] ?? INF);

        $rekomendasi = Lowongan::tampil()
            ->with(['usaha.desa', 'keahlian'])
            ->get()
            ->map(fn (Lowongan $l) => ['lowongan' => $l, 'hasil' => $this->skor->hitung($dataProfil, $this->dataLowongan($l))])
            ->filter(fn ($r) => $r['hasil']['skor'] >= $ambang)
            ->sortBy($terdekat
                ? [$urutJarak, $urutSkor, ['lowongan.id', 'asc']]
                : [$urutSkor, $urutJarak, ['lowongan.id', 'asc']])
            ->values();

        $keahlian = $rekomendasi->pluck('hasil.keahlian_kurang')->flatten()->unique()->all();
        $pelatihan = $this->saranPelatihan($keahlian)->groupBy('keahlian_id')->all();

        return $rekomendasi->map(fn (array $item) => $item + [
            'pelatihan' => collect(Arr::only($pelatihan, $item['hasil']['keahlian_kurang']))->collapse(),
        ]);
    }

    /** Saran pelatihan untuk keahlian yang belum dimiliki (F-12). */
    public function saranPelatihan(array $keahlianIds): Collection
    {
        if ($keahlianIds === []) {
            return collect();
        }

        return Pelatihan::with('keahlian')->whereIn('keahlian_id', $keahlianIds)->orderBy('judul')->orderBy('id')->get();
    }

    private function dataProfil(ProfilPencari $p): array
    {
        $p->loadMissing(['user.desa', 'keahlian']);
        $desa = $p->user?->desa;

        return [
            'jurusan' => $p->jurusan,
            'keahlian_ids' => $p->keahlian->pluck('id')->all(),
            'desa_id' => $desa?->id,
            'kecamatan' => $desa?->kecamatan,
            'lintang' => $desa?->lintang,
            'bujur' => $desa?->bujur,
            'pendidikan' => $p->pendidikan_terakhir,
        ];
    }

    private function dataLowongan(Lowongan $l): array
    {
        $l->loadMissing(['usaha.desa', 'keahlian']);
        $desa = $l->usaha?->desa;

        return [
            'jurusan_target' => $l->jurusan_target,
            'keahlian_wajib_ids' => $l->keahlian->filter(fn ($k) => (bool) $k->pivot->wajib)->pluck('id')->all(),
            'desa_id' => $desa?->id,
            'kecamatan' => $desa?->kecamatan,
            'lintang' => $desa?->lintang,
            'bujur' => $desa?->bujur,
            'pendidikan_min' => $l->pendidikan_min,
        ];
    }
}
