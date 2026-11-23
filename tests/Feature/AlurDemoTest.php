<?php

namespace Tests\Feature;

use App\Models\Lowongan;
use App\Models\Usaha;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menguji langkah-langkah skenario demo (Rancangan Bab 8.3). */
class AlurDemoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_rizky_melihat_teknisi_jaringan_di_urutan_pertama(): void
    {
        $rizky = User::where('email', 'rizky@lokerin.test')->first();

        $this->actingAs($rizky)->get(route('pencari.beranda'))
            ->assertOk()
            ->assertSeeInOrder(['Teknisi Jaringan', 'Admin dan Kasir']);
    }

    public function test_lowongan_usaha_belum_terverifikasi_tidak_tampil(): void
    {
        $usaha = Usaha::where('nama_usaha', 'Kedai Sumber Rasa')->first();
        $usaha->lowongan()->create(['judul' => 'Lowongan Tersembunyi', 'pendidikan_min' => 'SMA', 'status' => 'aktif']);

        $this->get(route('lowongan.index'))->assertOk()->assertDontSee('Lowongan Tersembunyi');
    }

    public function test_kader_memverifikasi_usaha_di_desanya(): void
    {
        $kader = User::where('email', 'kader.mangliawan@lokerin.test')->first();
        $usaha = Usaha::where('nama_usaha', 'Kedai Sumber Rasa')->first();

        $this->actingAs($kader)->post(route('kader.verifikasi.update', $usaha), [
            'status' => 'terverifikasi', 'versi_profil' => $usaha->versiVerifikasi(),
        ])->assertRedirect();
        $this->assertSame('terverifikasi', $usaha->fresh()->status_verifikasi);
    }

    public function test_kader_tidak_bisa_memverifikasi_usaha_desa_lain(): void
    {
        $kader = User::where('email', 'kader.pucangsongo@lokerin.test')->first();
        $usaha = Usaha::where('nama_usaha', 'Kedai Sumber Rasa')->first();

        $this->actingAs($kader)->post(route('kader.verifikasi.update', $usaha), ['status' => 'terverifikasi'])->assertForbidden();
    }

    public function test_pencari_tidak_bisa_membuka_dasbor_kader(): void
    {
        $rizky = User::where('email', 'rizky@lokerin.test')->first();
        $this->actingAs($rizky)->get(route('kader.dasbor'))->assertForbidden();
    }

    public function test_rizky_bisa_melamar(): void
    {
        $rizky = User::where('email', 'rizky@lokerin.test')->first();
        $lowongan = Lowongan::where('judul', 'Teknisi Jaringan')->first();

        $this->actingAs($rizky)->post(route('pencari.lamar', $lowongan))->assertRedirect(route('pencari.lamaran'));
        $this->assertDatabaseHas('lamaran', ['lowongan_id' => $lowongan->id, 'skor_cocok' => 85, 'status' => 'dikirim']);
    }
}
