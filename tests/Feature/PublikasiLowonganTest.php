<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublikasiLowonganTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_dan_ringkasan_hanya_memuat_lowongan_publik_yang_masih_tersedia(): void
    {
        $this->travelTo(Carbon::parse('2026-11-23 00:30', 'Asia/Jakarta'));
        $desa = Desa::create(['nama' => 'Pucangsongo']);
        $tetangga = Desa::create(['nama' => 'Sekarpuro']);
        $luar = Desa::create(['nama' => 'Sawojajar', 'kecamatan' => 'Kedungkandang']);
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $usaha = $this->usaha($desa, noHp: '081987650001');
        $aktif = $usaha->lowongan()->create([
            'judul' => 'Kasir papan desa', 'jenis_kerja' => 'paruh_waktu', 'pendidikan_min' => 'SMA', 'tanggal_tutup' => '2026-11-23',
            'deskripsi' => 'Catatan panjang uji yang tidak dipakai pada ringkasan',
        ]);
        $this->usaha($tetangga)->lowongan()->create(['judul' => 'Teknisi desa tetangga']);
        $this->usaha($luar)->lowongan()->create(['judul' => 'Lowongan luar kecamatan']);
        $usaha->lowongan()->create(['judul' => 'Tawaran masih draf', 'status' => 'draf']);
        $usaha->lowongan()->create(['judul' => 'Tawaran sudah ditutup', 'status' => 'ditutup']);
        $usaha->lowongan()->create(['judul' => 'Tawaran sudah kedaluwarsa', 'tanggal_tutup' => '2026-11-22']);
        $this->usaha($desa, 'menunggu')->lowongan()->create(['judul' => 'Tawaran dari usaha menunggu']);
        $penuh = $usaha->lowongan()->create(['judul' => 'Tawaran kuota penuh', 'kuota' => 1]);
        $pencari = User::factory()->create(['role' => 'pencari', 'desa_id' => $desa->id, 'name' => 'Pelamar Rahasia Uji']);
        $pencari->profilPencari()->create()->lamaran()->create(['lowongan_id' => $penuh->id, 'status' => 'diterima'])
            ->penempatan()->create([]);

        $halaman = route('kader.publikasi-lowongan');
        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()->assertSee($halaman);
        $respon = $this->get($halaman)->assertOk()->assertSee('Cetak untuk papan desa')->assertSee('Ringkasan untuk WhatsApp RW')
            ->assertSee('23/11/2026 00:30 WIB')->assertSee('Kasir papan desa')->assertSee('Teknisi desa tetangga')
            ->assertDontSee('Lowongan luar kecamatan')->assertDontSee('Tawaran masih draf')->assertDontSee('Tawaran sudah ditutup')
            ->assertDontSee('Tawaran sudah kedaluwarsa')->assertDontSee('Tawaran dari usaha menunggu')->assertDontSee('Tawaran kuota penuh')
            ->assertDontSee('Pemilik Rahasia Uji')->assertDontSee('Alamat internal uji')->assertDontSee('081987650001')
            ->assertDontSee('Pelamar Rahasia Uji')->assertDontSee('Catatan panjang uji');
        $ringkasan = $respon->viewData('ringkasan');
        $this->assertStringContainsString(route('lowongan.detail', $aktif), $ringkasan);
        $this->assertStringContainsString('Paruh waktu · Pendidikan min. SMA · Batas melamar 23/11/2026', $ringkasan);
        $this->assertStringContainsString('Teknisi desa tetangga', $ringkasan);

        $this->get(route('kader.publikasi-lowongan', ['cakupan' => 'desa', 'desa_id' => $tetangga->id]))->assertOk()
            ->assertSee('Lowongan aktif Desa Pucangsongo')->assertSee(route('lowongan.index', ['desa' => $desa->id]))
            ->assertSee('Kasir papan desa')->assertDontSee('Teknisi desa tetangga');
    }

    public function test_daftar_cetak_lengkap_dan_ringkasan_muat_sepuluh_tenggat_terdekat(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $usaha = $this->usaha($desa);
        $usaha->lowongan()->create(['judul' => 'Tanpa tenggat', 'pendidikan_min' => 'SMP']);
        foreach (range(12, 1) as $hari) {
            $usaha->lowongan()->create([
                'judul' => sprintf('Pekerjaan tenggat %02d', $hari),
                'tanggal_tutup' => now('Asia/Jakarta')->addDays($hari)->toDateString(),
            ]);
        }

        $halaman = $this->actingAs($kader)->get(route('kader.publikasi-lowongan'))->assertOk()
            ->assertSee('Pekerjaan tenggat 12')->assertSee('Tanpa tenggat');
        $ringkasan = $halaman->viewData('ringkasan');
        $this->assertCount(13, $halaman->viewData('lowongan'));
        $this->assertStringContainsString('1. Pekerjaan tenggat 01', $ringkasan);
        $this->assertStringContainsString('10. Pekerjaan tenggat 10', $ringkasan);
        $this->assertStringContainsString('Ringkasan 10 dari 13 lowongan dalam cakupan ini.', $ringkasan);
        $this->assertStringNotContainsString('Pekerjaan tenggat 11', $ringkasan);
        $this->assertStringNotContainsString('Tanpa tenggat', $ringkasan);
    }

    public function test_publikasi_terbatas_untuk_kader_dengan_desa(): void
    {
        $halaman = route('kader.publikasi-lowongan');
        $this->get($halaman)->assertRedirect(route('login'));
        $pencari = User::factory()->create(['role' => 'pencari']);
        $this->actingAs($pencari)->get($halaman)->assertForbidden();
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => null]);
        $this->actingAs($kader)->get($halaman)->assertForbidden();

        $desa = Desa::create(['nama' => 'Pucangsongo']);
        $kader->update(['desa_id' => $desa->id]);
        $this->actingAs($kader->fresh())->get($halaman)->assertOk()->assertSee('Belum ada lowongan aktif untuk cakupan ini.')
            ->assertDontSee('Salin ringkasan')->assertDontSee('Cetak untuk papan desa');
    }

    private function usaha(Desa $desa, string $status = 'terverifikasi', ?string $noHp = null): Usaha
    {
        $pemilik = User::factory()->create(['role' => 'usaha', 'name' => 'Pemilik Rahasia Uji', 'no_hp' => $noHp]);

        return $pemilik->usaha()->create([
            'nama_usaha' => 'Usaha uji', 'desa_id' => $desa->id, 'status_verifikasi' => $status, 'alamat' => 'Alamat internal uji',
        ]);
    }
}
