<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LowonganUsahaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usaha_menyimpan_draf_menerbitkan_dan_menutup_lowongan_paruh_waktu(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $wajib = Keahlian::create(['nama' => 'Kasir']);
        $tambahan = Keahlian::create(['nama' => 'Pelayanan pelanggan']);
        $lowongan = $this->dataLowongan() + ['keahlian' => [$wajib->id => 'wajib', $tambahan->id => 'tambahan']];

        $this->actingAs($pemilik)->get(route('usaha.lowongan.create'))->assertRedirect(route('usaha.profil'));
        $this->get(route('usaha.profil'))->assertOk()->assertSee('Profil usaha');
        $this->put(route('usaha.profil.update'), $this->dataUsaha($desa) + ['status_verifikasi' => 'terverifikasi', 'is_umkm' => '1'])
            ->assertRedirect(route('usaha.beranda'));

        $usaha = $pemilik->usaha()->sole();
        $this->assertSame('menunggu', $usaha->status_verifikasi);
        $this->assertTrue($usaha->is_umkm);
        $this->get(route('usaha.beranda'))->assertOk()->assertSee('Buat lowongan');
        $this->get(route('usaha.lowongan.create'))->assertOk()->assertSee('Paruh waktu')->assertDontSee('value="aktif"', false);
        $this->post(route('usaha.lowongan.store'), $lowongan + ['status' => 'aktif'])->assertSessionHasErrors('status');
        $this->assertDatabaseCount('lowongan', 0);
        $this->post(route('usaha.lowongan.store'), $lowongan + ['status' => 'draf'])->assertRedirect(route('usaha.beranda'));

        $draf = $usaha->lowongan()->sole();
        $this->assertNull($draf->diterbitkan_at);
        $this->get(route('lowongan.detail', $draf))->assertNotFound();
        $this->actingAs($kader)->post(route('kader.verifikasi.update', $usaha), [
            'status' => 'terverifikasi', 'versi_profil' => $usaha->versiVerifikasi(),
        ])->assertRedirect();
        $this->actingAs($pemilik)->get(route('usaha.lowongan.edit', $draf))->assertOk()->assertSee('Kelola lowongan');
        $this->put(route('usaha.lowongan.update', $draf), $lowongan + ['status' => 'aktif'])->assertRedirect(route('usaha.beranda'));
        $terbit = $draf->fresh()->diterbitkan_at;
        $this->assertNotNull($terbit);

        $this->assertDatabaseHas('lowongan_keahlian', ['lowongan_id' => $draf->id, 'keahlian_id' => $wajib->id, 'wajib' => true]);
        $this->assertDatabaseHas('lowongan_keahlian', ['lowongan_id' => $draf->id, 'keahlian_id' => $tambahan->id, 'wajib' => false]);
        $this->get(route('lowongan.index', ['jenis' => 'paruh_waktu']))->assertOk()->assertSee($lowongan['judul']);
        $this->get(route('lowongan.index', ['jenis' => 'penuh_waktu']))->assertOk()->assertDontSee($lowongan['judul']);
        $this->get(route('lowongan.detail', $draf))->assertOk()->assertSee('Rp75.000')->assertSee('Batas melamar');

        $orangLain = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $this->actingAs($orangLain)->get(route('usaha.lowongan.edit', $draf))->assertNotFound();
        $this->put(route('usaha.lowongan.update', $draf), $lowongan + ['status' => 'ditutup'])->assertNotFound();
        $this->actingAs($pemilik)->put(route('usaha.lowongan.update', $draf), $lowongan + ['status' => 'ditutup'])
            ->assertRedirect(route('usaha.beranda'));
        $this->assertEquals($terbit, $draf->fresh()->diterbitkan_at);
        $this->get(route('lowongan.detail', $draf))->assertNotFound();
        $this->get(route('lowongan.index'))->assertOk()->assertDontSee($lowongan['judul']);

        $pencari = User::factory()->create(['role' => 'pencari', 'desa_id' => $desa->id]);
        $pencari->profilPencari()->create(['persetujuan_data_at' => now()]);
        $this->actingAs($pencari)->post(route('pencari.lamar', $draf))->assertNotFound();
    }

    public function test_perubahan_profil_memerlukan_verifikasi_ulang_dari_desa_baru(): void
    {
        $asal = Desa::create(['nama' => 'Mangliawan']);
        $tujuan = Desa::create(['nama' => 'Sekarpuro']);
        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $asal->id]);
        $kaderAsal = User::factory()->create(['role' => 'kader', 'desa_id' => $asal->id]);
        $kaderTujuan = User::factory()->create(['role' => 'kader', 'desa_id' => $tujuan->id]);
        $usaha = $pemilik->usaha()->create($this->dataUsaha($asal) + ['status_verifikasi' => 'terverifikasi']);
        $lowongan = $usaha->lowongan()->create($this->dataLowongan() + ['status' => 'aktif']);

        $this->actingAs($pemilik)->put(route('usaha.profil.update'), $this->dataUsaha($asal))->assertRedirect();
        $this->assertSame('terverifikasi', $usaha->fresh()->status_verifikasi);
        $this->get(route('lowongan.detail', $lowongan))->assertOk();

        $this->put(route('usaha.profil.update'), $this->dataUsaha($tujuan))->assertRedirect();
        $this->assertSame('menunggu', $usaha->fresh()->status_verifikasi);
        $this->assertDatabaseCount('usaha', 1);
        $this->get(route('usaha.profil'))->assertOk()->assertSee($usaha->nama_usaha);
        $this->get(route('lowongan.detail', $lowongan))->assertNotFound();
        $this->actingAs($kaderAsal)->post(route('kader.verifikasi.update', $usaha), ['status' => 'terverifikasi'])->assertForbidden();
        $versiLama = $usaha->versiVerifikasi();
        $this->actingAs($kaderTujuan)->post(route('kader.verifikasi.update', $usaha), [
            'status' => 'terverifikasi', 'versi_profil' => $versiLama,
        ])->assertSessionHasErrors('versi_profil');
        $this->assertSame('menunggu', $usaha->fresh()->status_verifikasi);
        $this->get(route('kader.verifikasi'))->assertOk()->assertSee($usaha->fresh()->versiVerifikasi());
        $this->post(route('kader.verifikasi.update', $usaha), [
            'status' => 'terverifikasi', 'versi_profil' => $usaha->fresh()->versiVerifikasi(),
        ])->assertSessionDoesntHaveErrors();
        $this->get(route('lowongan.detail', $lowongan))->assertOk()->assertSee('Sekarpuro');
    }

    public function test_batas_melamar_memakai_tanggal_pakis_dan_keahlian_harus_terdaftar(): void
    {
        $this->travelTo(Carbon::parse('2026-11-23 18:00:00', 'UTC'));
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $usaha = $pemilik->usaha()->create($this->dataUsaha($desa) + ['status_verifikasi' => 'terverifikasi']);
        $data = $this->dataLowongan() + ['status' => 'aktif'];
        $kedaluwarsa = array_replace($data, ['tanggal_tutup' => '2026-11-23']);

        $this->actingAs($pemilik)->post(route('usaha.lowongan.store'), $kedaluwarsa)->assertSessionHasErrors('tanggal_tutup');
        $this->post(route('usaha.lowongan.store'), $data + ['keahlian' => [123456 => 'wajib']])->assertSessionHasErrors('keahlian');
        $this->assertDatabaseCount('lowongan', 0);

        $lama = $usaha->lowongan()->create($kedaluwarsa);
        $hariIni = $usaha->lowongan()->create(array_replace($data, ['tanggal_tutup' => '2026-11-24']));
        $this->get(route('lowongan.detail', $lama))->assertNotFound();
        $this->get(route('lowongan.detail', $hariIni))->assertOk();
    }

    private function dataUsaha(Desa $desa): array
    {
        return ['nama_usaha' => 'Kedai Uji', 'jenis_usaha' => 'Kuliner', 'desa_id' => $desa->id, 'alamat' => 'Jalan Uji 101'];
    }

    private function dataLowongan(): array
    {
        return [
            'judul' => 'Kasir akhir pekan', 'deskripsi' => 'Membantu transaksi kedai.', 'jenis_kerja' => 'paruh_waktu',
            'pendidikan_min' => 'SMA', 'kuota' => 1, 'gaji_min' => 75000, 'tanggal_tutup' => now()->addWeek()->toDateString(),
        ];
    }
}
