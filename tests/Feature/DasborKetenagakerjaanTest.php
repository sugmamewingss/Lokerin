<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Lowongan;
use App\Models\ProfilPencari;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DasborKetenagakerjaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_indikator_memakai_periode_wib_dan_menghitung_warga_dan_umkm_unik(): void
    {
        $this->travelTo(Carbon::parse('2026-07-10 08:00', 'Asia/Jakarta'));
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $desaLain = Desa::create(['nama' => 'Sekarpuro']);
        $luarPakis = Desa::create(['nama' => 'Sawojajar', 'kecamatan' => 'Kedungkandang']);
        $warga = $this->profil($desa, '2025-12-31 17:00:00');
        $wargaKedua = $this->profil($desa, '2026-01-15 00:00:00');
        $wargaLama = $this->profil($desa, '2025-12-31 16:59:59');
        $tanpaPersetujuan = $this->profil($desa, null);
        $this->profil($desaLain, '2026-01-02 09:00:00');
        $this->profil($luarPakis, '2026-01-02 09:00:00');

        $umkm = $this->usaha($desa, true);
        $aktif = $this->lowongan($umkm, '2025-12-31 17:00:00');
        $ditutup = $this->lowongan($umkm, '2026-02-01 08:00:00', 'ditutup');
        $this->lowongan($this->usaha($desa, true), '2025-12-31 16:59:59');
        $this->lowongan($this->usaha($desa, true), '2026-06-30 17:00:00');
        $this->lowongan($this->usaha($desa, true), null, 'draf');
        $this->lowongan($this->usaha($desa, false), '2026-02-01 08:00:00');
        $this->lowongan($this->usaha($desa, null), '2026-02-01 08:00:00');
        $this->lowongan($this->usaha($desa, true, 'menunggu'), '2026-02-01 08:00:00');
        $pekerjaanLuarDesa = $this->lowongan($this->usaha($desaLain, true), '2026-02-01 08:00:00');

        $this->tempatkan($warga, $aktif, '2026-03-01 08:00:00', '2026-04-01 08:00:00');
        $this->tempatkan($warga, $pekerjaanLuarDesa, '2026-03-02 08:00:00', '2026-06-30 16:59:59');
        $this->tempatkan($wargaKedua, $aktif, '2026-06-30 17:00:00', '2026-07-01 08:00:00');
        $this->tempatkan($wargaLama, $ditutup, '2026-06-30 16:59:59', '2026-06-30 17:00:00');
        $this->tempatkan($tanpaPersetujuan, $aktif, '2026-03-01 08:00:00', '2026-04-01 08:00:00');
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $disnaker = User::factory()->create(['role' => 'disnaker']);
        $periode = ['mulai' => '2026-01-01'];

        $kaderView = $this->actingAs($kader)->get(route('kader.dasbor', $periode))->assertOk()
            ->assertSee('01/01/2026–30/06/2026 WIB')->assertSee('50,0%')->assertSee('BPS/Sakernas')->assertSee('TKJ')
            ->assertViewHas('angka', fn ($angka) => $angka === [
                'Pencari kerja terdaftar' => 3, 'Lowongan aktif di desa' => 5, 'Lamaran bulan ini' => 1, 'Penempatan' => 4,
            ]);
        $indikator = $kaderView->viewData('ujiCoba');
        $this->assertSame(2, $indikator['pendaftar']);
        $this->assertSame(1, $indikator['warga_ditempatkan']);
        $this->assertSame(1, $indikator['umkm']);
        $this->assertEquals(50, $indikator['persen_penempatan']);

        $this->actingAs($disnaker)->get(route('disnaker.dasbor', $periode))->assertOk()->assertDontSee('Sawojajar')
            ->assertViewHas('ujiCoba', fn ($ujiCoba) => $ujiCoba == $indikator)
            ->assertViewHas('rekap', fn ($rekap) => $rekap->count() === 2 && $rekap->first()['penempatan'] === 4)
            ->assertDontSee($warga->user->name);
        $this->get(route('disnaker.dasbor'))->assertOk()->assertSee('Semua catatan tersedia')
            ->assertViewHas('ujiCoba', fn ($ujiCoba) => $ujiCoba['pendaftar'] === 3 && $ujiCoba['umkm'] === 3);
    }

    public function test_indikator_tanpa_pendaftar_dan_batas_akses_desa(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $sekitar = Desa::create(['nama' => 'Sekarpuro']);
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $kaderLain = User::factory()->create(['role' => 'kader', 'desa_id' => $sekitar->id]);

        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()
            ->assertSee('Belum ada pendaftar')->assertViewHas('ujiCoba', fn ($ujiCoba) => $ujiCoba['persen_penempatan'] === null);
        $this->get(route('kader.dasbor', ['mulai' => '2026-02-30']))->assertRedirect(route('kader.dasbor'))
            ->assertSessionHasErrors('mulai');
        $this->get(route('kader.dasbor', ['mulai' => now('Asia/Jakarta')->addDay()->toDateString()]))
            ->assertRedirect(route('kader.dasbor'))->assertSessionHasErrors('mulai');
        $this->actingAs($kaderLain)->get(route('kader.dasbor', ['desa_id' => $desa->id]))->assertOk()
            ->assertSee('Sekarpuro')->assertDontSee('Indikator uji coba Mangliawan');
        $this->get(route('disnaker.dasbor'))->assertForbidden();
    }

    private function profil(Desa $desa, ?string $persetujuan): ProfilPencari
    {
        $user = User::factory()->create(['role' => 'pencari', 'desa_id' => $desa->id]);

        return $user->profilPencari()->create(['jurusan' => 'TKJ', 'persetujuan_data_at' => $persetujuan]);
    }

    private function usaha(Desa $desa, ?bool $umkm, string $status = 'terverifikasi'): Usaha
    {
        $user = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);

        return $user->usaha()->create(['nama_usaha' => 'Usaha uji', 'desa_id' => $desa->id, 'status_verifikasi' => $status, 'is_umkm' => $umkm]);
    }

    private function lowongan(Usaha $usaha, ?string $terbit, string $status = 'aktif'): Lowongan
    {
        $lowongan = $usaha->lowongan()->make(['judul' => 'Lowongan uji', 'status' => $status, 'kuota' => 5]);
        $lowongan->diterbitkan_at = $terbit;
        $lowongan->save();

        return $lowongan;
    }

    private function tempatkan(ProfilPencari $profil, Lowongan $lowongan, string $dikirim, string $dicatat): void
    {
        $lamaran = $profil->lamaran()->create(['lowongan_id' => $lowongan->id, 'status' => 'diterima', 'dikirim_at' => $dikirim]);
        $lamaran->penempatan()->make()->forceFill(['created_at' => $dicatat])->save();
    }
}
