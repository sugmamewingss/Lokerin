<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\Lowongan;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RekomendasiLokasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_warga_bisa_memprioritaskan_lokasi_dan_melihat_alasan_rekomendasi(): void
    {
        $asal = Desa::create(['nama' => 'Desa asal', 'lintang' => -7.97000, 'bujur' => 112.67000]);
        $dekat = Desa::create(['nama' => 'Desa dekat', 'lintang' => -7.97000, 'bujur' => 112.67500]);
        $menengah = Desa::create(['nama' => 'Desa menengah', 'lintang' => -7.97000, 'bujur' => 112.68500]);
        $jauh = Desa::create(['nama' => 'Desa jauh', 'lintang' => -7.97000, 'bujur' => 112.71000]);
        $tanpaTitik = Desa::create(['nama' => 'Desa tanpa titik']);

        $pencari = User::factory()->create(['role' => 'pencari', 'desa_id' => $asal->id]);
        $pencari->profilPencari()->create(['jurusan' => 'TKJ', 'pendidikan_terakhir' => 'SMK', 'persetujuan_data_at' => now()]);
        $keahlianWajib = Keahlian::create(['nama' => 'Konfigurasi router']);
        $keahlianTambahan = Keahlian::create(['nama' => 'Dokumentasi jaringan']);

        $this->buatLowongan($tanpaTitik, 'Lokasi tanpa koordinat');
        $this->buatLowongan($jauh, 'Posisi desa jauh');
        $this->buatLowongan($menengah, 'Posisi desa menengah');
        $lowonganDekat = $this->buatLowongan($dekat, 'Posisi desa dekat');
        $lowonganDekat->keahlian()->attach([$keahlianWajib->id => ['wajib' => true], $keahlianTambahan->id => ['wajib' => false]]);
        $rendah = $lowonganDekat->usaha->lowongan()->create(['judul' => 'Skor di bawah ambang', 'jurusan_target' => 'DKV']);
        $rendah->keahlian()->attach($keahlianWajib->id, ['wajib' => true]);
        $lowonganDekat->usaha->lowongan()->create(['judul' => 'Lowongan sudah ditutup', 'jurusan_target' => 'TKJ', 'status' => 'ditutup']);

        $this->actingAs($pencari)->get(route('pencari.beranda'))->assertOk()
            ->assertSeeInOrder(['Posisi desa menengah', 'Posisi desa jauh', 'Lokasi tanpa koordinat', 'Posisi desa dekat']);

        $this->get(route('pencari.beranda', ['urut' => 'jarak']))->assertOk()
            ->assertSeeInOrder(['Posisi desa dekat', 'Posisi desa menengah', 'Posisi desa jauh', 'Lokasi tanpa koordinat'])
            ->assertSee('Jarak belum tersedia')
            ->assertDontSee('Skor di bawah ambang')
            ->assertDontSee('Lowongan sudah ditutup');

        $this->get(route('lowongan.detail', $lowonganDekat))->assertOk()
            ->assertSee('Jurusan sesuai')
            ->assertSee('0 dari 1 keahlian wajib terpenuhi')
            ->assertSee('Sekitar 0,6 km antarpusat desa')
            ->assertSee('(wajib, belum dimiliki)')
            ->assertSee('(tambahan)');
    }

    public function test_semua_desa_pakis_memiliki_titik_referensi_dari_seeder(): void
    {
        $this->seed(DesaSeeder::class);

        $this->assertSame(15, Desa::where('kecamatan', 'Pakis')->whereNotNull('lintang')->whereNotNull('bujur')->count());
        $this->assertDatabaseHas('desa', ['nama' => 'Bunutwetan', 'lintang' => -7.94162, 'bujur' => 112.71481]);
    }

    private function buatLowongan(Desa $desa, string $judul): Lowongan
    {
        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $usaha = $pemilik->usaha()->create(['nama_usaha' => 'Usaha contoh', 'desa_id' => $desa->id, 'status_verifikasi' => 'terverifikasi']);

        return $usaha->lowongan()->create(['judul' => $judul, 'jurusan_target' => 'TKJ']);
    }
}
