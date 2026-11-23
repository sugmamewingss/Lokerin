<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivasiDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_persetujuan_mengaktifkan_profil_dan_bisa_ditarik(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $pencari = User::factory()->create(['role' => 'pencari', 'desa_id' => $desa->id]);
        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $usaha = $pemilik->usaha()->create(['nama_usaha' => 'Kedai Uji', 'desa_id' => $desa->id, 'status_verifikasi' => 'terverifikasi']);
        $lowongan = $usaha->lowongan()->create(['judul' => 'Kasir Paruh Waktu', 'jenis_kerja' => 'paruh_waktu']);
        $data = ['desa_id' => $desa->id, 'pendidikan_terakhir' => 'SMK', 'jurusan' => 'TKJ', 'status_kerja' => 'belum_bekerja'];

        $this->get(route('privasi'))->assertOk()->assertSeeInOrder(['Publik', 'Internal', 'Terbatas']);
        $this->actingAs($pencari)->get(route('pencari.profil'))->assertOk()->assertSee(route('privasi'));
        $this->put(route('pencari.profil'), $data + ['persetujuan_data_at' => now()])->assertSessionHasErrors('setuju');
        $this->post(route('pencari.lamar', $lowongan))->assertForbidden();

        $this->put(route('pencari.profil'), $data + ['setuju' => '1'])->assertRedirect(route('pencari.beranda'));
        $profil = $pencari->profilPencari()->sole();
        $this->assertTrue($profil->isAktif());
        $this->assertDatabaseHas('log_akses', ['user_id' => $pencari->id, 'aksi' => 'persetujuan_data', 'objek' => "profil#{$profil->id}"]);
        $this->put(route('pencari.profil'), $data)->assertRedirect(route('pencari.beranda'));
        $this->assertEquals($profil->persetujuan_data_at, $profil->fresh()->persetujuan_data_at);
        $this->get(route('pencari.beranda'))->assertOk()->assertSee($lowongan->judul);
        $this->get(route('lowongan.detail', $lowongan))->assertOk()->assertSee('Lamar sekarang');
        $this->post(route('pencari.lamar', $lowongan))->assertRedirect(route('pencari.lamaran'));
        $lamaran = $profil->lamaran()->sole();
        $this->assertFalse($lamaran->kontakTerbuka());
        $lamaran->update(['status' => 'wawancara']);
        $this->assertTrue($lamaran->kontakTerbuka());

        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()
            ->assertViewHas('angka', fn ($angka) => $angka['Pencari kerja terdaftar'] === 1 && $angka['Lamaran bulan ini'] === 1);
        $this->actingAs($pencari)->delete(route('pencari.persetujuan.tarik'))->assertRedirect(route('pencari.profil'));
        $this->assertFalse($profil->fresh()->isAktif());
        $this->assertFalse($lamaran->fresh()->kontakTerbuka());
        $this->assertDatabaseHas('log_akses', ['user_id' => $pencari->id, 'aksi' => 'tarik_persetujuan_data', 'objek' => "profil#{$profil->id}"]);
        $this->get(route('pencari.beranda'))->assertRedirect(route('pencari.profil'));
        $this->get(route('lowongan.detail', $lowongan))->assertOk()->assertDontSee('Lamar sekarang');
        $this->post(route('pencari.lamar', $lowongan))->assertForbidden();
        $this->get(route('pencari.lamaran'))->assertOk()->assertSee($lowongan->judul);
        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()
            ->assertViewHas('angka', fn ($angka) => $angka['Pencari kerja terdaftar'] === 0 && $angka['Lamaran bulan ini'] === 0)
            ->assertViewHas('perJurusan', fn ($jurusan) => $jurusan->isEmpty());
        $this->actingAs($pemilik)->get(route('usaha.beranda'))->assertOk()->assertSee('0 pelamar');

        $disnaker = User::factory()->create(['role' => 'disnaker']);
        $this->actingAs($disnaker)->get(route('disnaker.dasbor'))->assertOk()
            ->assertViewHas('rekap', fn ($rekap) => $rekap->first()['pencari'] === 0 && $rekap->first()['lamaran'] === 0);
    }

    public function test_publik_dan_peran_lain_tidak_menerima_data_terbatas(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $desaLain = Desa::create(['nama' => 'Pucangsongo']);
        $pemilik = User::factory()->create(['role' => 'usaha', 'name' => 'Pemilik Pribadi', 'no_hp' => '081234596789', 'desa_id' => $desa->id]);
        $usaha = $pemilik->usaha()->create([
            'nama_usaha' => 'Kedai Publik', 'alamat' => 'Alamat Khusus 48', 'desa_id' => $desa->id, 'status_verifikasi' => 'terverifikasi',
        ]);
        $lowongan = $usaha->lowongan()->create(['judul' => 'Kasir', 'status' => 'aktif']);

        $this->get(route('lowongan.detail', $lowongan))->assertOk()->assertSee($usaha->nama_usaha)
            ->assertDontSee($usaha->alamat)->assertDontSee($pemilik->name)->assertDontSee($pemilik->no_hp)->assertDontSee($pemilik->email);
        $this->get(route('pencari.profil'))->assertRedirect(route('login'));
        $this->get(route('usaha.profil'))->assertRedirect(route('login'));

        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $this->actingAs($kader)->get(route('kader.verifikasi'))->assertOk()->assertSee($usaha->alamat)->assertDontSee($pemilik->no_hp);
        $this->get(route('pencari.profil'))->assertForbidden();
        $this->delete(route('pencari.persetujuan.tarik'))->assertForbidden();

        $kaderLain = User::factory()->create(['role' => 'kader', 'desa_id' => $desaLain->id]);
        $this->actingAs($kaderLain)->get(route('kader.verifikasi'))->assertOk()->assertDontSee($usaha->nama_usaha)->assertDontSee($usaha->alamat);
        $pencari = User::factory()->create(['role' => 'pencari', 'desa_id' => $desa->id]);
        $this->actingAs($pencari)->get(route('kader.verifikasi'))->assertForbidden();
        $this->get(route('disnaker.dasbor'))->assertForbidden();
        $this->get(route('usaha.profil'))->assertForbidden();
        $this->actingAs($pemilik)->get(route('pencari.lamaran'))->assertForbidden();
    }
}
