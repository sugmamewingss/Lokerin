<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\Pelatihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogPelatihanTest extends TestCase
{
    use RefreshDatabase;

    public function test_disnaker_mengelola_keahlian_dan_publikasi_pelatihan(): void
    {
        $disnaker = User::factory()->create(['role' => 'disnaker']);
        $warga = User::factory()->create(['role' => 'pencari']);
        $kelola = route('disnaker.pelatihan.index');
        $simpan = route('disnaker.pelatihan.store');
        $keahlian = ['nama' => 'Administrasi digital', 'kategori' => 'Perkantoran'];

        $this->get($kelola)->assertRedirect(route('login'));
        $this->actingAs($warga)->post($simpan, [])->assertForbidden();
        $this->post(route('disnaker.keahlian.store'), $keahlian)->assertForbidden();
        $this->actingAs($disnaker)->get($kelola)->assertOk()->assertSee('Tambah pelatihan');
        $this->get(route('disnaker.keahlian.index'))->assertOk()->assertSee('Tambah keahlian');
        $this->post(route('disnaker.keahlian.store'), $keahlian)->assertRedirect();
        $this->post(route('disnaker.keahlian.store'), $keahlian)->assertSessionHasErrors('nama');
        $skill = Keahlian::sole();
        $this->from(route('disnaker.keahlian.index'))->put(route('disnaker.keahlian.update', $skill), [
            'nama' => 'Administrasi berbasis digital', 'kategori' => 'Perkantoran',
        ])->assertRedirect(route('disnaker.keahlian.index'));
        $this->get(route('disnaker.pelatihan.create'))->assertOk()->assertSee('Administrasi berbasis digital')
            ->assertSeeInOrder(['BLK', 'LPK', 'SMK']);

        $data = [
            'judul' => 'Dasar administrasi digital', 'penyelenggara' => 'BLK Pakis (contoh)', 'jenis_penyelenggara' => 'blk',
            'keahlian_id' => $skill->id, 'jadwal' => 'Rabu dan Jumat', 'lokasi' => 'Balai latihan Pakis', 'kuota' => 20,
        ];
        $this->post($simpan, array_replace($data, ['jenis_penyelenggara' => 'palsu', 'keahlian_id' => 999, 'kuota' => 0]))
            ->assertSessionHasErrors(['jenis_penyelenggara', 'keahlian_id', 'kuota']);
        $this->assertDatabaseCount('pelatihan', 0);
        $this->post($simpan, $data)->assertRedirect($kelola);
        $pelatihan = Pelatihan::sole();
        $detail = route('pelatihan.show', $pelatihan);

        $this->get(route('pelatihan.index', ['keahlian' => $skill->id]))->assertOk()->assertSee($detail);
        $this->get(route('pelatihan.index', ['keahlian' => 999]))->assertOk()->assertDontSee($data['judul']);
        $this->get($detail)->assertOk()->assertSee('Administrasi berbasis digital')->assertSee('20 peserta');
        $this->get(route('disnaker.pelatihan.edit', $pelatihan))->assertOk()->assertSee('Rabu dan Jumat');
        $this->put(route('disnaker.pelatihan.update', $pelatihan), array_replace($data, ['jadwal' => 'Sabtu pagi']))->assertRedirect($kelola);
        $this->get($detail)->assertOk()->assertSee('Sabtu pagi');
        $this->delete(route('disnaker.pelatihan.destroy', $pelatihan))->assertRedirect($kelola);
        $this->assertSoftDeleted($pelatihan);
        $this->get($kelola)->assertOk()->assertSee('Pulihkan');
        $this->get($detail)->assertNotFound();
        $this->get(route('pelatihan.index'))->assertOk()->assertDontSee($data['judul']);
        $this->patch(route('disnaker.pelatihan.pulihkan', $pelatihan))->assertRedirect($kelola);
        $this->get($detail)->assertOk()->assertSee('Sabtu pagi');
    }

    public function test_katalog_menaut_ke_lowongan_terbuka_dan_saran_memakai_keahlian_yang_kurang(): void
    {
        $desa = Desa::create(['nama' => 'Sekarpuro']);
        $pencari = User::factory()->create(['role' => 'pencari', 'desa_id' => $desa->id]);
        $profil = $pencari->profilPencari()->create(['pendidikan_terakhir' => 'SMK', 'persetujuan_data_at' => now()]);
        $router = Keahlian::create(['nama' => 'Konfigurasi router']);
        $excel = Keahlian::create(['nama' => 'Excel']);
        $opsional = Keahlian::create(['nama' => 'Dokumentasi']);
        $profil->keahlian()->attach($excel);
        $latihanRouter = $router->pelatihan()->create(['judul' => 'Kelas router Pakis', 'penyelenggara' => 'SMK Pakis', 'jenis_penyelenggara' => 'smk']);
        $excel->pelatihan()->create(['judul' => 'Kelas Excel Pakis', 'penyelenggara' => 'BLK Pakis', 'jenis_penyelenggara' => 'blk']);
        $opsional->pelatihan()->create(['judul' => 'Kelas dokumentasi', 'penyelenggara' => 'LPK Pakis', 'jenis_penyelenggara' => 'lpk']);
        $arsip = $router->pelatihan()->create(['judul' => 'Pelatihan router terdahulu', 'penyelenggara' => 'SMK Pakis']);
        $arsip->delete();

        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $usaha = $pemilik->usaha()->create(['nama_usaha' => 'Usaha contoh', 'desa_id' => $desa->id, 'status_verifikasi' => 'terverifikasi']);
        $buka = $usaha->lowongan()->create(['judul' => 'Teknisi masih tersedia', 'pendidikan_min' => 'SMA']);
        $buka->keahlian()->attach([$router->id => ['wajib' => true], $excel->id => ['wajib' => true], $opsional->id => ['wajib' => false]]);
        $cadangan = $usaha->lowongan()->create(['judul' => 'Router hanya tambahan']);
        $cadangan->keahlian()->attach($router, ['wajib' => false]);
        $tutup = $usaha->lowongan()->create(['judul' => 'Lowongan sudah tutup', 'status' => 'ditutup']);
        $tutup->keahlian()->attach($router);
        $lewat = $usaha->lowongan()->create(['judul' => 'Lowongan sudah lewat', 'tanggal_tutup' => '2020-01-01']);
        $lewat->keahlian()->attach($router);
        $penuh = $usaha->lowongan()->create(['judul' => 'Lowongan kuota penuh', 'kuota' => 1]);
        $penuh->keahlian()->attach($router);
        $profil->lamaran()->create(['lowongan_id' => $penuh->id, 'status' => 'diterima'])->penempatan()->create([]);
        $belumVerifikasi = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $belumVerifikasi->usaha()->create(['nama_usaha' => 'Usaha baru', 'desa_id' => $desa->id])
            ->lowongan()->create(['judul' => 'Lowongan belum diverifikasi'])->keahlian()->attach($router);

        $tautan = route('pelatihan.show', $latihanRouter);
        $this->get($tautan)->assertOk()->assertSee(route('lowongan.detail', $buka))
            ->assertDontSee('Router hanya tambahan')->assertDontSee('Lowongan sudah tutup')->assertDontSee('Lowongan sudah lewat')
            ->assertDontSee('Lowongan kuota penuh')->assertDontSee('Lowongan belum diverifikasi');
        $detailLowongan = route('lowongan.detail', $buka);
        $this->get($detailLowongan)->assertOk()->assertSee($tautan)->assertSee('Kelas Excel Pakis')->assertDontSee('Kelas dokumentasi');
        $this->actingAs($pencari)->get($detailLowongan)->assertOk()->assertSee($tautan)
            ->assertDontSee('Kelas Excel Pakis')->assertDontSee('Kelas dokumentasi')->assertDontSee('Pelatihan router terdahulu');
        $this->get(route('pencari.beranda'))->assertOk()->assertSee($detailLowongan.'#pelatihan-terkait');

        $latihanRouter->delete();
        $this->get($detailLowongan)->assertOk()->assertDontSee('Kelas router Pakis');
        $this->get(route('pencari.beranda'))->assertOk()->assertDontSee($detailLowongan.'#pelatihan-terkait');
    }
}
