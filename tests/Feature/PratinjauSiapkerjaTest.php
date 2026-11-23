<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PratinjauSiapkerjaTest extends TestCase
{
    use RefreshDatabase;

    public function test_hanya_disnaker_yang_bisa_membuka_pratinjau_lokal(): void
    {
        $pratinjau = route('disnaker.siapkerja.index');
        $this->get($pratinjau)->assertRedirect(route('login'));
        foreach ([User::ROLE_PENCARI, User::ROLE_USAHA, User::ROLE_KADER] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get($pratinjau)->assertForbidden();
        }

        $disnaker = User::factory()->create(['role' => User::ROLE_DISNAKER]);
        $this->actingAs($disnaker)->get(route('disnaker.dasbor'))->assertOk()->assertSee($pratinjau);
        $this->get($pratinjau)->assertOk()->assertSee('Halaman ini menampilkan pratinjau lokal.')
            ->assertSee('Belum ada lowongan publik yang masih menerima lamaran di Pakis.')
            ->assertSee('https://paskerid.kemnaker.go.id/news/86');
        $this->post($pratinjau)->assertMethodNotAllowed();
    }

    public function test_pratinjau_hanya_memuat_informasi_publik_dari_lowongan_pakis_yang_masih_terbuka(): void
    {
        $this->travelTo(Carbon::parse('2026-11-23 00:30', 'Asia/Jakarta'));
        $pakis = Desa::create(['nama' => 'Pucangsongo']);
        $luar = Desa::create(['nama' => 'Sawojajar', 'kecamatan' => 'Kedungkandang']);
        $usaha = $this->usaha($pakis, noHp: '081111111999');
        $aktif = $usaha->lowongan()->create([
            'judul' => 'Kasir paruh waktu uji', 'jenis_kerja' => 'paruh_waktu', 'pendidikan_min' => 'SMA',
            'tanggal_tutup' => '2026-11-23', 'deskripsi' => 'Kontak dalam deskripsi uji',
        ]);
        $usaha->lowongan()->create(['judul' => 'Lowongan tanpa tenggat']);
        $usaha->lowongan()->create(['judul' => 'Lowongan draf uji', 'status' => 'draf']);
        $usaha->lowongan()->create(['judul' => 'Lowongan ditutup uji', 'status' => 'ditutup']);
        $usaha->lowongan()->create(['judul' => 'Lowongan kedaluwarsa uji', 'tanggal_tutup' => '2026-11-22']);
        $this->usaha($pakis, 'menunggu')->lowongan()->create(['judul' => 'Lowongan belum diverifikasi']);
        $this->usaha($luar)->lowongan()->create(['judul' => 'Lowongan di luar Pakis']);
        $penuh = $usaha->lowongan()->create(['judul' => 'Lowongan penuh uji', 'kuota' => 1]);

        $pelamar = User::factory()->create(['role' => User::ROLE_PENCARI, 'name' => 'Identitas pelamar uji']);
        $pelamar->profilPencari()->create(['persetujuan_data_at' => now()])->lamaran()
            ->create(['lowongan_id' => $penuh->id, 'status' => 'diterima'])->penempatan()->create([]);

        $disnaker = User::factory()->create(['role' => User::ROLE_DISNAKER]);
        $hasil = $this->actingAs($disnaker)->get(route('disnaker.siapkerja.index'))->assertOk()
            ->assertSee('Kasir paruh waktu uji')->assertSee('Usaha publik uji')->assertSee('Desa Pucangsongo')
            ->assertSee('Paruh waktu')->assertSee('Pendidikan min. SMA')->assertSee('23/11/2026')
            ->assertSee(route('lowongan.detail', $aktif))->assertSee('Lowongan tanpa tenggat')
            ->assertDontSee('Lowongan draf uji')->assertDontSee('Lowongan ditutup uji')->assertDontSee('Lowongan kedaluwarsa uji')
            ->assertDontSee('Lowongan penuh uji')->assertDontSee('Lowongan belum diverifikasi')->assertDontSee('Lowongan di luar Pakis')
            ->assertDontSee('Identitas pelamar uji')->assertDontSee('Identitas pemilik uji')->assertDontSee('Alamat internal uji')
            ->assertDontSee('Kontak dalam deskripsi uji')->assertDontSee('081111111999');
        $this->assertStringContainsString('no-store', $hasil->headers->get('Cache-Control'));
    }

    private function usaha(Desa $desa, string $status = 'terverifikasi', ?string $noHp = null): Usaha
    {
        $pemilik = User::factory()->create([
            'role' => User::ROLE_USAHA, 'name' => 'Identitas pemilik uji',
            'no_hp' => $noHp,
        ]);

        return $pemilik->usaha()->create([
            'nama_usaha' => 'Usaha publik uji', 'desa_id' => $desa->id,
            'status_verifikasi' => $status, 'alamat' => 'Alamat internal uji',
        ]);
    }
}
