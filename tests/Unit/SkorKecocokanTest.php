<?php

namespace Tests\Unit;

use App\Services\SkorKecocokan;
use PHPUnit\Framework\TestCase;

/** Memastikan rumus sama dengan contoh di Rancangan Lokerin Tabel 4.3. */
class SkorKecocokanTest extends TestCase
{
    private SkorKecocokan $skor;
    private array $rizky;

    protected function setUp(): void
    {
        $this->skor = new SkorKecocokan(require __DIR__.'/../../config/lokerin.php');
        // keahlian: 1 perbaikan komputer, 2 Excel, 3 komunikasi
        $this->rizky = ['jurusan' => 'TKJ', 'keahlian_ids' => [1, 2, 3], 'desa_id' => 1, 'kecamatan' => 'Pakis', 'pendidikan' => 'SMK'];
    }

    public function test_teknisi_jaringan_di_desa_sendiri_skor_85(): void
    {
        $hasil = $this->skor->hitung($this->rizky, [
            'jurusan_target' => 'TKJ', 'keahlian_wajib_ids' => [4, 1], 'desa_id' => 1, 'kecamatan' => 'Pakis', 'pendidikan_min' => 'SMA',
        ]);
        $this->assertSame(85, $hasil['skor']);
        $this->assertSame([4], $hasil['keahlian_kurang']);
    }

    public function test_admin_kasir_desa_tetangga_skor_82(): void
    {
        $hasil = $this->skor->hitung($this->rizky, [
            'jurusan_target' => null, 'keahlian_wajib_ids' => [2, 3, 5], 'desa_id' => 2, 'kecamatan' => 'Pakis', 'pendidikan_min' => 'SMA',
        ]);
        $this->assertSame(82, $hasil['skor']);
    }

    public function test_desainer_di_luar_pakis_skor_14_dan_tidak_direkomendasikan(): void
    {
        $hasil = $this->skor->hitung($this->rizky, [
            'jurusan_target' => 'DKV', 'keahlian_wajib_ids' => [6, 7], 'desa_id' => 99, 'kecamatan' => 'Kedungkandang', 'pendidikan_min' => 'SMA',
        ]);
        $this->assertSame(14, $hasil['skor']);
        $this->assertLessThan(50, $hasil['skor']);
    }

    public function test_jurusan_serumpun_dapat_setengah(): void
    {
        $this->assertSame(0.5, $this->skor->nilaiJurusan('RPL', 'TKJ'));
    }

    public function test_pendidikan_kurang_dapat_nol(): void
    {
        $this->assertSame(0.0, $this->skor->nilaiPendidikan('SMP', 'SMA'));
        $this->assertSame(1.0, $this->skor->nilaiPendidikan('SMK', 'SMA'));
    }
}
