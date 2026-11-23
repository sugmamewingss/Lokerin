<?php

namespace Tests\Feature;

use App\Enums\StatusAk1;
use App\Models\Desa;
use App\Models\PermohonanAk1;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermohonanAk1Test extends TestCase
{
    use RefreshDatabase;

    public function test_warga_mengajukan_lalu_melacak_perbaikan_rujukan_dan_penerimaan_kartu(): void
    {
        $pakiskembar = Desa::create(['nama' => 'Pakiskembar']);
        $warga = User::factory()->create(['role' => User::ROLE_PENCARI, 'desa_id' => $pakiskembar->id]);
        $kader = User::factory()->create(['role' => User::ROLE_KADER, 'desa_id' => $pakiskembar->id]);
        $pengajuan = route('ak1.store');

        $this->get(route('ak1.panduan'))->assertOk()->assertSee('Ini simulasi pendampingan AK/1 Pakiskembar')
            ->assertSee('https://panduan.kemnaker.go.id/panduan/20');
        $this->post($pengajuan, ['setuju_ak1' => '1'])->assertRedirect(route('login'));
        $this->actingAs($warga)->get($pengajuan)->assertOk()->assertSee('Ajukan pendampingan');
        $this->post($pengajuan)->assertSessionHasErrors('setuju_ak1');
        $this->assertDatabaseCount('permohonan_ak1', 0);
        $hasil = $this->post($pengajuan, ['setuju_ak1' => '1', 'status' => 'selesai']);
        $permohonan = PermohonanAk1::sole();
        $detail = route('ak1.show', $permohonan);
        $hasil->assertRedirect($detail);
        $this->assertSame(StatusAk1::Diajukan, $permohonan->status);
        $this->post($pengajuan, ['setuju_ak1' => '1'])->assertSessionHasErrors('setuju_ak1');
        $this->assertDatabaseCount('permohonan_ak1', 1);

        $this->patch($detail, ['status' => 'selesai'])->assertSessionHasErrors('status');
        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()->assertSee('Pendampingan AK/1');
        $this->get(route('ak1.index'))->assertOk()->assertSee($warga->name);
        $this->patch($detail, ['status' => 'perlu_dilengkapi'])->assertRedirect($detail);
        $this->patch($detail, ['status' => 'dirujuk'])->assertSessionHasErrors('status');
        $this->actingAs($warga)->get($detail)->assertOk()->assertSee('Ajukan kembali');
        $this->patch($detail, ['status' => 'diajukan'])->assertRedirect($detail);
        $this->actingAs($kader)->patch($detail, ['status' => 'dirujuk'])->assertRedirect($detail);
        $this->get($detail)->assertOk()->assertDontSee('Konfirmasi kartu diterima');
        $this->actingAs($warga)->get($detail)->assertOk()->assertSee('Konfirmasi kartu diterima');
        $this->patch($detail, ['status' => 'selesai'])->assertRedirect($detail);
        $this->patch($detail, ['status' => 'dibatalkan'])->assertSessionHasErrors('status');
        $akhir = $this->get($detail)->assertOk()->assertSeeInOrder([
            'Menunggu pemeriksaan kader', 'Perlu dilengkapi', 'Menunggu pemeriksaan kader', 'Dirujuk ke layanan resmi', 'Selesai',
        ])->assertSee('WIB')->assertDontSee($warga->no_hp);
        $this->assertStringContainsString('no-store', $akhir->headers->get('Cache-Control'));
        $this->assertSame(StatusAk1::Selesai, $permohonan->fresh()->status);
        $this->assertCount(5, $permohonan->riwayat());
        $this->post($pengajuan, ['setuju_ak1' => '1'])->assertRedirect();
        $this->assertDatabaseCount('permohonan_ak1', 2);
    }

    public function test_kader_mendampingi_warga_aktif_setelah_persetujuan_dan_warga_bisa_membatalkan(): void
    {
        $pakiskembar = Desa::create(['nama' => 'Pakiskembar']);
        $kader = User::factory()->create(['role' => User::ROLE_KADER, 'desa_id' => $pakiskembar->id]);
        $nomor = '081111110028';
        $pendaftaran = $this->actingAs($kader)->post(route('kader.warga.store'), ['name' => 'Warga AK/1 Uji', 'no_hp' => $nomor])->assertOk();
        $warga = User::where('no_hp', $nomor)->sole();
        $pengajuan = route('ak1.store');
        $this->post($pengajuan, ['nomor_warga_ak1' => $nomor, 'setuju_ak1' => '1'])->assertSessionHasErrors('nomor_warga_ak1');
        $this->assertNull(session()->getOldInput('nomor_warga_ak1'));
        $this->post(route('logout'))->assertRedirect();
        $this->post(route('aktivasi-warga.store', $warga), [
            'kode_aktivasi' => parse_url($pendaftaran->viewData('tautan'), PHP_URL_FRAGMENT),
            'password' => 'SandiWargaAk1Uji',
            'password_confirmation' => 'SandiWargaAk1Uji',
        ])->assertRedirect(route('pencari.profil'));
        $this->actingAs($kader)->post($pengajuan, ['nomor_warga_ak1' => $nomor])->assertSessionHasErrors('setuju_ak1');
        $this->assertDatabaseCount('permohonan_ak1', 0);

        $hasil = $this->post($pengajuan, ['nomor_warga_ak1' => $nomor, 'setuju_ak1' => '1']);
        $permohonan = PermohonanAk1::sole();
        $detail = route('ak1.show', $permohonan);
        $hasil->assertRedirect($detail);
        $this->assertSame($warga->id, $permohonan->pemohon_id);
        $this->assertSame($kader->id, $permohonan->riwayat()->sole()->user_id);
        $this->patch($detail, ['status' => 'dibatalkan'])->assertSessionHasErrors('status');
        $this->patch($detail, ['status' => 'perlu_dilengkapi'])->assertRedirect($detail);
        $this->patch($detail, ['status' => 'diajukan'])->assertRedirect($detail);
        $this->actingAs($warga->fresh())->get(route('ak1.index'))->assertOk()->assertSee($detail)->assertDontSee($nomor);
        $this->patch($detail, ['status' => 'dibatalkan'])->assertRedirect($detail);
        $this->get($detail)->assertOk()->assertSee('Warga telah membatalkan permohonan.');
        $this->assertSame(StatusAk1::Dibatalkan, $permohonan->fresh()->status);
    }

    public function test_permohonan_terbatas_untuk_pemilik_dan_kader_pakiskembar(): void
    {
        $pakiskembar = Desa::create(['nama' => 'Pakiskembar']);
        $desaLain = Desa::create(['nama' => 'Pakiskembar', 'kecamatan' => 'Kecamatan lain']);
        $warga = User::factory()->create(['role' => User::ROLE_PENCARI, 'desa_id' => $pakiskembar->id]);
        $wargaSedesa = User::factory()->create(['role' => User::ROLE_PENCARI, 'desa_id' => $pakiskembar->id]);
        $wargaLain = User::factory()->create(['role' => User::ROLE_PENCARI, 'desa_id' => $desaLain->id]);
        $kader = User::factory()->create(['role' => User::ROLE_KADER, 'desa_id' => $pakiskembar->id]);
        $kaderLain = User::factory()->create(['role' => User::ROLE_KADER, 'desa_id' => $desaLain->id]);
        $disnaker = User::factory()->create(['role' => User::ROLE_DISNAKER]);
        $pengajuan = route('ak1.store');

        $this->actingAs($warga)->post($pengajuan, ['setuju_ak1' => '1'])->assertRedirect();
        $detail = route('ak1.show', PermohonanAk1::sole());
        $this->actingAs($wargaSedesa)->get(route('ak1.index'))->assertOk()->assertDontSee($detail);
        $this->get($detail)->assertNotFound();
        $this->patch($detail, ['status' => 'dibatalkan'])->assertNotFound();
        $this->actingAs($wargaLain)->get(route('ak1.index'))->assertOk()->assertDontSee('Catat permohonan');
        $this->post($pengajuan, ['setuju_ak1' => '1'])->assertForbidden();
        $this->actingAs($kaderLain)->get($detail)->assertForbidden();
        $this->patch($detail, ['status' => 'dirujuk'])->assertForbidden();
        $this->actingAs($disnaker)->get($detail)->assertForbidden();
        $this->actingAs($kader)->post($pengajuan, ['nomor_warga_ak1' => $wargaLain->no_hp, 'setuju_ak1' => '1'])
            ->assertSessionHasErrors('nomor_warga_ak1');
        $this->assertDatabaseCount('permohonan_ak1', 1);
        $this->assertSame(StatusAk1::Diajukan, PermohonanAk1::sole()->status);
    }
}
