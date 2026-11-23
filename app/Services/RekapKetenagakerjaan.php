<?php

namespace App\Services;

use App\Models\Desa;
use App\Models\Lamaran;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RekapKetenagakerjaan
{
    public const KECAMATAN = 'Pakis';

    public const DESA_UJI_COBA = 'Mangliawan';

    public function perDesa(Builder $desa): Collection
    {
        $awalBulan = CarbonImmutable::now('Asia/Jakarta')->startOfMonth();
        $lamaran = Lamaran::denganPersetujuan()
            ->whereHas('profil.user', fn ($q) => $q->whereColumn('users.desa_id', 'desa.id'))
            ->selectRaw('COUNT(*)');

        return $desa->select(['id', 'nama'])
            ->withCount([
                'profilPencari as pencari' => fn ($q) => $q->disetujui(),
                'lowongan as lowongan_aktif' => fn ($q) => $q->tampil(),
            ])
            ->addSelect([
                'lamaran' => (clone $lamaran),
                'lamaran_bulan_ini' => (clone $lamaran)->where('dikirim_at', '>=', $awalBulan->utc())
                    ->where('dikirim_at', '<', $awalBulan->addMonth()->utc()),
                'penempatan' => (clone $lamaran)->ditempatkan(),
            ])
            ->orderBy('nama')->get()->map(fn (Desa $item) => [
                'desa' => $item->nama,
                'pencari' => (int) $item->pencari,
                'lowongan' => (int) $item->lowongan_aktif,
                'lamaran' => (int) $item->lamaran,
                'lamaran_bulan_ini' => (int) $item->lamaran_bulan_ini,
                'penempatan' => (int) $item->penempatan,
            ]);
    }

    public function ujiCoba(Desa $desa, ?CarbonImmutable $mulai): array
    {
        $akhir = $mulai?->addMonthsNoOverflow(6);
        $periode = fn (Builder $q, string $kolom) => $mulai
            ? $q->where($kolom, '>=', $mulai->utc())->where($kolom, '<', $akhir->utc())
            : $q;
        $pendaftar = fn (Builder $q) => $periode($q->disetujui(), 'profil_pencari.persetujuan_data_at');

        $hasil = Desa::whereKey($desa->id)->withCount([
            'profilPencari as pendaftar' => $pendaftar,
            'profilPencari as warga_ditempatkan' => fn ($q) => $pendaftar($q)->whereHas('lamaran',
                fn ($q) => $q->ditempatkan(fn ($q) => $periode($q, 'penempatan.created_at'))),
            'usaha as umkm' => fn ($q) => $q->terverifikasi()->where('is_umkm', true)
                ->whereHas('lowongan', fn ($q) => $periode($q->whereNotNull('diterbitkan_at'), 'lowongan.diterbitkan_at')),
        ])->firstOrFail();

        return [
            'mulai' => $mulai,
            'sampai' => $akhir?->subDay(),
            'pendaftar' => (int) $hasil->pendaftar,
            'warga_ditempatkan' => (int) $hasil->warga_ditempatkan,
            'umkm' => (int) $hasil->umkm,
            'persen_penempatan' => $hasil->pendaftar > 0 ? 100 * $hasil->warga_ditempatkan / $hasil->pendaftar : null,
        ];
    }
}
