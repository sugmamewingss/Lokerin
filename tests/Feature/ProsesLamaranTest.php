<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\LogAkses;
use App\Models\Lowongan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProsesLamaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_usaha_memproses_lamaran_sampai_penempatan_dan_rekap_desa(): void
    {
        $desa = Desa::create(['nama' => 'Sekarpuro']);
        [$pemilik, $lowongan] = $this->lowongan($desa);
        $rizky = $this->pencari($desa, 'Rizky Uji', 'TKJ', '081234560001');
        $ayu = $this->pencari($desa, 'Ayu Uji', 'Akuntansi', '081234560002');
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $daftarPelamar = route('usaha.pelamar.index', $lowongan);

        $this->actingAs($ayu)->post(route('pencari.lamar', $lowongan))->assertRedirect(route('pencari.lamaran'));
        $this->actingAs($rizky)->post(route('pencari.lamar', $lowongan))->assertRedirect(route('pencari.lamaran'));
        $lamaran = $rizky->profilPencari->lamaran()->sole();
        $lamaranAyu = $ayu->profilPencari->lamaran()->sole();
        $ubah = route('usaha.pelamar.update', [$lowongan, $lamaran]);
        $ubahAyu = route('usaha.pelamar.update', [$lowongan, $lamaranAyu]);
        $terima = ['status' => 'diterima', 'tanggal_mulai' => '2026-12-01', 'jenis_kontrak' => 'paruh_waktu', 'terdaftar_bpjs' => '1'];

        $this->actingAs($pemilik)->get(route('usaha.beranda'))->assertOk()->assertSee($daftarPelamar);
        $halaman = $this->get($daftarPelamar)->assertOk()->assertSeeInOrder(['Rizky Uji', 'Ayu Uji'])
            ->assertSee('Tandai sudah dilihat')->assertDontSee($rizky->no_hp)->assertDontSee($ayu->no_hp);
        $this->assertStringContainsString('no-store', $halaman->headers->get('Cache-Control'));
        $this->patch($ubah, $terima)->assertSessionHasErrors('status');
        $this->assertSame('dikirim', $lamaran->fresh()->status);
        $this->assertDatabaseCount('penempatan', 0);

        $this->patch($ubah, ['status' => 'dilihat'])->assertRedirect();
        $this->get($daftarPelamar)->assertOk()->assertSee('Panggil wawancara')->assertDontSee($rizky->no_hp);
        $this->patch($ubah, ['status' => 'wawancara'])->assertRedirect();
        $this->get($daftarPelamar)->assertOk()->assertSee($rizky->no_hp)->assertDontSee($ayu->no_hp)
            ->assertSee('Terima dan catat penempatan');
        $this->patch($ubah, ['status' => 'diterima'])->assertSessionHasErrors(['tanggal_mulai', 'jenis_kontrak', 'terdaftar_bpjs']);
        $this->assertSame('wawancara', $lamaran->fresh()->status);
        $this->assertDatabaseCount('penempatan', 0);

        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()->assertViewHas('angka', fn ($angka) => $angka['Penempatan'] === 0);
        $this->actingAs($rizky)->patch(route('pencari.lamaran.batal', $lamaran))->assertSessionHasErrors('status');
        $this->actingAs($pemilik)->patch($ubah, $terima)->assertRedirect(route('usaha.pelamar.index', ['lowongan' => $lowongan, 'page' => 1]));
        $this->patch($ubah, $terima)->assertRedirect();
        $this->assertDatabaseCount('penempatan', 1);
        $this->assertDatabaseHas('lamaran', ['id' => $lamaran->id, 'status' => 'diterima']);
        $this->assertSame('2026-12-01', $lamaran->penempatan()->sole()->tanggal_mulai->toDateString());
        $this->assertDatabaseHas('penempatan', ['lamaran_id' => $lamaran->id, 'jenis_kontrak' => 'paruh_waktu', 'terdaftar_bpjs' => true]);
        $this->assertDatabaseHas('log_akses', ['user_id' => $pemilik->id, 'aksi' => 'ubah_status_lamaran', 'objek' => "lamaran#{$lamaran->id}:diterima"]);
        $this->assertSame(1, LogAkses::where('objek', "lamaran#{$lamaran->id}:diterima")->count());

        $this->patch($ubahAyu, ['status' => 'dilihat'])->assertRedirect();
        $this->patch($ubahAyu, ['status' => 'wawancara'])->assertRedirect();
        $this->patch($ubahAyu, $terima)->assertSessionHasErrors('status');
        $this->assertSame('wawancara', $lamaranAyu->fresh()->status);
        $this->patch($ubahAyu, ['status' => 'ditolak'])->assertRedirect();
        $this->get($daftarPelamar)->assertOk()->assertSee('0 dari 1 slot penempatan tersisa')->assertDontSee($ayu->no_hp);
        $this->get(route('lowongan.detail', $lowongan))->assertNotFound();
        $this->get(route('lowongan.index'))->assertOk()->assertDontSee($lowongan->judul);
        $this->actingAs($rizky)->get(route('pencari.lamaran'))->assertOk()->assertSee('Diterima')->assertSee('01/12/2026')
            ->assertSee('Paruh waktu')->assertSee('Terdaftar')->assertDontSee('Batalkan lamaran');
        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()->assertViewHas('angka', fn ($angka) => $angka['Penempatan'] === 1);
        $disnaker = User::factory()->create(['role' => 'disnaker']);
        $this->actingAs($disnaker)->get(route('disnaker.dasbor'))->assertOk()->assertViewHas('rekap', fn ($rekap) => $rekap->first()['penempatan'] === 1);
    }

    public function test_pencari_bisa_membatalkan_lamarannya_sendiri_sebelum_wawancara(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        [$pemilik, $lowongan] = $this->lowongan($desa);
        $pencari = $this->pencari($desa, 'Pelamar Uji', 'TKJ', '081234560003');
        $orangLain = $this->pencari($desa, 'Pencari Lain', 'TKJ', '081234560004');
        $lamaran = $pencari->profilPencari->lamaran()->create(['lowongan_id' => $lowongan->id, 'status' => 'dilihat']);
        $batal = route('pencari.lamaran.batal', $lamaran);
        $ubah = route('usaha.pelamar.update', [$lowongan, $lamaran]);

        $this->actingAs($orangLain)->patch($batal)->assertNotFound();
        $this->actingAs($pemilik)->patch($batal)->assertForbidden();
        $this->patch($ubah, ['status' => 'dibatalkan'])->assertSessionHasErrors('status');
        $this->actingAs($pencari)->get(route('pencari.lamaran'))->assertOk()->assertSee('Batalkan lamaran');
        $this->patch($batal)->assertRedirect(route('pencari.lamaran'));
        $this->patch($batal)->assertRedirect(route('pencari.lamaran'));
        $this->assertDatabaseHas('lamaran', ['id' => $lamaran->id, 'status' => 'dibatalkan']);
        $this->assertDatabaseHas('log_akses', ['user_id' => $pencari->id, 'aksi' => 'ubah_status_lamaran', 'objek' => "lamaran#{$lamaran->id}:dibatalkan"]);
        $this->actingAs($pemilik)->patch($ubah, ['status' => 'wawancara'])->assertSessionHasErrors('status');
        $this->get(route('usaha.pelamar.index', $lowongan))->assertOk()->assertSee('Dibatalkan')->assertDontSee($pencari->no_hp);
    }

    public function test_akses_pelamar_mengikuti_kepemilikan_verifikasi_dan_persetujuan(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        [$pemilik, $lowongan] = $this->lowongan($desa);
        [$usahaLain] = $this->lowongan($desa);
        $lowonganLain = $pemilik->usaha->lowongan()->create(['judul' => 'Lowongan berbeda']);
        $pencari = $this->pencari($desa, 'Nama Terbatas', 'TKJ', '081234560005');
        $lamaran = $pencari->profilPencari->lamaran()->create(['lowongan_id' => $lowongan->id, 'status' => 'wawancara']);
        $daftar = route('usaha.pelamar.index', $lowongan);
        $ubah = route('usaha.pelamar.update', [$lowongan, $lamaran]);

        $this->actingAs($usahaLain)->get($daftar)->assertNotFound();
        $this->patch($ubah, ['status' => 'ditolak'])->assertNotFound();
        $this->actingAs($pemilik)->patch(route('usaha.pelamar.update', [$lowonganLain, $lamaran]), ['status' => 'ditolak'])->assertNotFound();
        $pemilik->usaha->update(['status_verifikasi' => 'menunggu']);
        $this->get($daftar)->assertForbidden();
        $this->patch($ubah, ['status' => 'ditolak'])->assertForbidden();
        $pemilik->usaha->update(['status_verifikasi' => 'terverifikasi']);
        $this->get($daftar)->assertOk()->assertSee($pencari->no_hp);

        $this->actingAs($pencari)->delete(route('pencari.persetujuan.tarik'))->assertRedirect();
        $this->actingAs($pemilik)->get($daftar)->assertOk()->assertDontSee($pencari->name)->assertDontSee($pencari->no_hp);
        $this->patch($ubah, ['status' => 'ditolak'])->assertNotFound();
        $this->assertSame('wawancara', $lamaran->fresh()->status);
    }

    public function test_kuota_tidak_bisa_diturunkan_di_bawah_penempatan_yang_sudah_tercatat(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        [$pemilik, $lowongan] = $this->lowongan($desa);
        $lowongan->update(['kuota' => 2]);
        foreach ([1, 2] as $nomor) {
            $pencari = $this->pencari($desa, "Pelamar $nomor", 'TKJ', "08123456001$nomor");
            $lamaran = $pencari->profilPencari->lamaran()->create(['lowongan_id' => $lowongan->id, 'status' => 'diterima']);
            $lamaran->penempatan()->create(['jenis_kontrak' => 'kontrak']);
        }
        $data = [
            'judul' => $lowongan->judul, 'deskripsi' => 'Menangani dukungan teknis.',
            'jenis_kerja' => 'penuh_waktu', 'pendidikan_min' => 'SMA', 'status' => 'aktif',
        ];

        $this->actingAs($pemilik)->put(route('usaha.lowongan.update', $lowongan), $data + ['kuota' => 1])->assertSessionHasErrors('kuota');
        $this->assertSame(2, $lowongan->fresh()->kuota);
        $this->get(route('lowongan.detail', $lowongan))->assertNotFound();
        $this->put(route('usaha.lowongan.update', $lowongan), $data + ['kuota' => 3])->assertRedirect(route('usaha.beranda'));
        $this->get(route('lowongan.detail', $lowongan))->assertOk();
    }

    private function lowongan(Desa $desa): array
    {
        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $usaha = $pemilik->usaha()->create(['nama_usaha' => 'Usaha Uji', 'desa_id' => $desa->id, 'status_verifikasi' => 'terverifikasi']);
        $lowongan = $usaha->lowongan()->create([
            'judul' => 'Teknisi Uji', 'jurusan_target' => 'TKJ', 'pendidikan_min' => 'SMA', 'kuota' => 1,
        ]);

        return [$pemilik, $lowongan];
    }

    private function pencari(Desa $desa, string $nama, string $jurusan, string $nomor): User
    {
        $user = User::factory()->create(['name' => $nama, 'no_hp' => $nomor, 'role' => 'pencari', 'desa_id' => $desa->id]);
        $user->profilPencari()->create(['jurusan' => $jurusan, 'pendidikan_terakhir' => 'SMK', 'persetujuan_data_at' => now()]);

        return $user;
    }
}
