<?php

namespace App\Services;

/**
 * Logika inti skor kecocokan Lokerin (Rancangan Bab 4.3).
 *
 *   Skor = 40 x J + 30 x K + 20 x D + 10 x E
 *
 * Kelas ini sengaja tidak bergantung pada database supaya mudah diuji
 * dan mudah dijelaskan. Penghubung ke model ada di MatchingService.
 */
class SkorKecocokan
{
    public function __construct(private array $config)
    {
    }

    /**
     * @param array{jurusan:?string, keahlian_ids:int[], desa_id:?int, kecamatan:?string, pendidikan:?string} $profil
     * @param array{jurusan_target:?string, keahlian_wajib_ids:int[], desa_id:?int, kecamatan:?string, pendidikan_min:?string} $lowongan
     * @return array{skor:int, J:float, K:float, D:float, E:float, keahlian_kurang:int[]}
     */
    public function hitung(array $profil, array $lowongan): array
    {
        $J = $this->nilaiJurusan($profil['jurusan'] ?? null, $lowongan['jurusan_target'] ?? null);

        $wajib = array_values(array_unique($lowongan['keahlian_wajib_ids'] ?? []));
        $dimiliki = array_values(array_unique($profil['keahlian_ids'] ?? []));
        $kurang = array_values(array_diff($wajib, $dimiliki));
        $K = count($wajib) === 0 ? 1.0 : (count($wajib) - count($kurang)) / count($wajib);

        $D = $this->nilaiLokasi($profil, $lowongan);
        $E = $this->nilaiPendidikan($profil['pendidikan'] ?? null, $lowongan['pendidikan_min'] ?? null);

        $b = $this->config['bobot'];
        $skor = (int) round($b['jurusan'] * $J + $b['keahlian'] * $K + $b['lokasi'] * $D + $b['pendidikan'] * $E);

        return ['skor' => $skor, 'J' => $J, 'K' => round($K, 2), 'D' => $D, 'E' => $E, 'keahlian_kurang' => $kurang];
    }

    public function nilaiJurusan(?string $jurusanProfil, ?string $jurusanTarget): float
    {
        if ($jurusanTarget === null || trim($jurusanTarget) === '') {
            return 1.0; // lowongan terbuka untuk semua jurusan
        }
        if ($jurusanProfil === null || trim($jurusanProfil) === '') {
            return 0.0;
        }
        if (strcasecmp(trim($jurusanProfil), trim($jurusanTarget)) === 0) {
            return 1.0;
        }
        $rumpunProfil = $this->rumpunDari($jurusanProfil);

        return $rumpunProfil !== null && $rumpunProfil === $this->rumpunDari($jurusanTarget) ? 0.5 : 0.0;
    }

    public function nilaiLokasi(array $profil, array $lowongan): float
    {
        $l = $this->config['lokasi'];
        if (!empty($profil['desa_id']) && ($profil['desa_id'] ?? null) === ($lowongan['desa_id'] ?? null)) {
            return $l['desa_sama'];
        }
        if (!empty($profil['kecamatan']) && strcasecmp($profil['kecamatan'], $lowongan['kecamatan'] ?? '') === 0) {
            return $l['kecamatan_sama'];
        }

        return $l['luar'];
    }

    public function nilaiPendidikan(?string $pendidikanProfil, ?string $pendidikanMin): float
    {
        $level = $this->config['level_pendidikan'];
        if ($pendidikanMin === null) {
            return 1.0;
        }

        return ($level[$pendidikanProfil] ?? 0) >= ($level[$pendidikanMin] ?? 0) ? 1.0 : 0.0;
    }

    private function rumpunDari(string $jurusan): ?string
    {
        foreach ($this->config['rumpun_jurusan'] as $rumpun => $daftar) {
            foreach ($daftar as $j) {
                if (strcasecmp($j, trim($jurusan)) === 0) {
                    return $rumpun;
                }
            }
        }

        return null;
    }
}
