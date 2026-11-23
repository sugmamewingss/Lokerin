<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PendaftaranWargaTest extends TestCase
{
    use RefreshDatabase;

    public function test_kader_mendaftarkan_warga_yang_mengaktifkan_akun_dan_menyetujui_data_sendiri(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $data = ['name' => 'Warga Uji', 'no_hp' => '081111110012'];
        $daftar = route('kader.warga.index');

        $this->actingAs($kader)->get(route('kader.dasbor'))->assertOk()->assertSee($daftar);
        $this->get($daftar)->assertOk()->assertSee('Daftarkan warga')->assertSee('Mangliawan');
        $respons = $this->post(route('kader.warga.store'), $data + [
            'role' => 'disnaker', 'setuju' => '1', 'persetujuan_data_at' => now(), 'password' => 'dari-kader',
        ])->assertOk()->assertViewIs('kader.tautan-aktivasi-warga');
        $this->assertStringContainsString('no-store', $respons->headers->get('Cache-Control'));
        $this->assertAuthenticatedAs($kader);

        $warga = User::where('no_hp', $data['no_hp'])->sole();
        $profil = $warga->profilPencari()->sole();
        $tautan = $respons->viewData('tautan');
        $kode = parse_url($tautan, PHP_URL_FRAGMENT);
        $this->assertSame('pencari', $warga->role);
        $this->assertSame($desa->id, $warga->desa_id);
        $this->assertTrue($warga->menunggu_aktivasi);
        $this->assertFalse(Hash::check('dari-kader', $warga->password));
        $this->assertSame($kader->id, $profil->didaftarkan_oleh);
        $this->assertNull($profil->persetujuan_data_at);
        $this->assertTrue(Password::tokenExists($warga, $kode));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $warga->email, 'token' => $kode]);
        $this->assertDatabaseHas('log_akses', ['user_id' => $kader->id, 'aksi' => 'pendaftaran_warga', 'objek' => "profil#{$profil->id}"]);
        $this->get($daftar)->assertOk()->assertSee('Warga Uji')->assertSee('Nomor berakhiran 0012')->assertDontSee($warga->no_hp);
        $this->get(route('kader.dasbor'))->assertOk()->assertViewHas('angka', fn ($angka) => $angka['Pencari kerja terdaftar'] === 0);

        $this->post(route('logout'))->assertRedirect();
        $this->get(route('aktivasi-warga.create', $warga))->assertOk()->assertSee('Aktivasi akun warga')->assertDontSee($warga->name);
        $aktivasi = route('aktivasi-warga.store', $warga);
        $this->post($aktivasi, ['kode_aktivasi' => $kode, 'password' => 'KataSandiWarga2026'])->assertSessionHasErrors('password')
            ->assertRedirect($tautan);
        $this->assertNull(session()->getOldInput('kode_aktivasi'));
        $this->assertGuest();

        $kataSandi = 'KataSandiWarga2026';
        $this->post($aktivasi, $this->dataAktivasi($kode, $kataSandi))->assertRedirect(route('pencari.profil'));
        $this->assertAuthenticatedAs($warga);
        $this->assertFalse($warga->fresh()->menunggu_aktivasi);
        $this->assertFalse(Password::tokenExists($warga, $kode));
        $this->assertNull($profil->fresh()->persetujuan_data_at);
        $this->get(route('pencari.beranda'))->assertRedirect(route('pencari.profil'));
        $this->put(route('pencari.profil'), [
            'desa_id' => $desa->id, 'pendidikan_terakhir' => 'SMK', 'status_kerja' => 'belum_bekerja', 'setuju' => '1',
        ])->assertRedirect(route('pencari.beranda'));
        $this->assertTrue($profil->fresh()->isAktif());
        $this->assertDatabaseHas('log_akses', ['user_id' => $warga->id, 'aksi' => 'persetujuan_data', 'objek' => "profil#{$profil->id}"]);

        $this->post(route('logout'))->assertRedirect();
        $this->post($aktivasi, $this->dataAktivasi($kode, $kataSandi))->assertNotFound();
        $this->post(route('login'), ['identitas' => $warga->no_hp, 'password' => $kataSandi])->assertRedirect(route('pencari.beranda'));
        $this->assertAuthenticatedAs($warga);
        $this->actingAs($kader)->get($daftar)->assertOk()->assertDontSee('Warga Uji');
        $this->post(route('kader.warga.tautan', $warga))->assertNotFound();
        $this->get(route('kader.dasbor'))->assertOk()->assertViewHas('angka', fn ($angka) => $angka['Pencari kerja terdaftar'] === 1);
    }

    public function test_hanya_kader_satu_desa_yang_dapat_memperbarui_tautan_dan_kode_berlaku_terbatas(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $desaLain = Desa::create(['nama' => 'Sekarpuro']);
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $kaderLain = User::factory()->create(['role' => 'kader', 'desa_id' => $desaLain->id]);
        $data = ['name' => 'Warga Mangliawan Uji', 'no_hp' => '081111110013'];
        $res = $this->actingAs($kader)->post(route('kader.warga.store'), $data)->assertOk();
        $warga = User::where('no_hp', $data['no_hp'])->sole();
        $kodeLama = parse_url($res->viewData('tautan'), PHP_URL_FRAGMENT);
        $perbarui = route('kader.warga.tautan', $warga);

        $this->actingAs($kaderLain)->get(route('kader.warga.index'))->assertOk()->assertDontSee($data['name']);
        $this->post($perbarui)->assertNotFound();
        $this->assertTrue(Password::tokenExists($warga, $kodeLama));
        $resBaru = $this->actingAs($kader)->post($perbarui)->assertOk();
        $kodeBaru = parse_url($resBaru->viewData('tautan'), PHP_URL_FRAGMENT);
        $this->assertFalse(Password::tokenExists($warga, $kodeLama));
        $this->assertTrue(Password::tokenExists($warga, $kodeBaru));
        $this->assertDatabaseHas('log_akses', ['user_id' => $kader->id, 'aksi' => 'perbarui_aktivasi_warga', 'objek' => "user#{$warga->id}"]);

        $warga->update(['password' => 'SandiPraAktivasi2026']);
        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login'), ['identitas' => $warga->no_hp, 'password' => 'SandiPraAktivasi2026'])->assertSessionHasErrors('identitas');
        $this->travel(61)->minutes();
        $this->post(route('aktivasi-warga.store', $warga), $this->dataAktivasi($kodeBaru, 'SandiBaruWarga2026'))->assertSessionHasErrors('kode_aktivasi');
        $this->assertGuest();
        $this->assertTrue($warga->fresh()->menunggu_aktivasi);
    }

    public function test_pendaftaran_membatasi_peran_desa_dan_nomor_yang_sudah_terdaftar(): void
    {
        $desa = Desa::create(['nama' => 'Mangliawan']);
        $desaLain = Desa::create(['nama' => 'Sekarpuro']);
        $kader = User::factory()->create(['role' => 'kader', 'desa_id' => $desa->id]);
        $tanpaDesa = User::factory()->create(['role' => 'kader', 'desa_id' => null]);
        $warga = User::factory()->create(['role' => 'pencari', 'desa_id' => $desaLain->id, 'no_hp' => '081111110014']);
        $simpan = route('kader.warga.store');
        $data = ['name' => 'Warga Baru Uji', 'no_hp' => '081111110015'];

        $this->post($simpan, $data)->assertRedirect(route('login'));
        $this->actingAs($warga)->post($simpan, $data)->assertForbidden();
        $this->actingAs($tanpaDesa)->post($simpan, $data)->assertForbidden();
        $this->actingAs($kader)->post($simpan, $data + ['desa_id' => $desaLain->id])->assertSessionHasErrors('desa_id');
        $this->post($simpan, array_replace($data, ['no_hp' => $warga->no_hp]))->assertSessionHasErrors('no_hp');
        $this->post($simpan, array_replace($data, ['no_hp' => 'tes-123']))->assertSessionHasErrors('no_hp');
        $this->assertSame($desaLain->id, $warga->fresh()->desa_id);
        $this->assertFalse($warga->fresh()->menunggu_aktivasi);
        $this->assertDatabaseMissing('users', ['no_hp' => $data['no_hp']]);
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_pendaftaran_mandiri_tetap_membolehkan_masuk_dengan_nomor_hp(): void
    {
        $desa = Desa::create(['nama' => 'Sekarpuro']);
        $daftar = [
            'name' => 'Warga Mandiri', 'no_hp' => '081111110016', 'role' => 'pencari', 'desa_id' => $desa->id,
            'password' => 'SandiWargaMandiri2026', 'password_confirmation' => 'SandiWargaMandiri2026',
        ];

        $this->post(route('register'), $daftar)->assertRedirect(route('pencari.profil'));
        $warga = User::where('no_hp', $daftar['no_hp'])->sole();
        $this->assertFalse($warga->menunggu_aktivasi);
        $this->assertNull($warga->profilPencari()->sole()->didaftarkan_oleh);
        $this->post(route('logout'))->assertRedirect();
        $this->post(route('login'), ['identitas' => $warga->no_hp, 'password' => $daftar['password']])->assertRedirect(route('pencari.beranda'));
        $this->assertAuthenticatedAs($warga);
    }

    private function dataAktivasi(string $kode, string $kataSandi): array
    {
        return ['kode_aktivasi' => $kode, 'password' => $kataSandi, 'password_confirmation' => $kataSandi];
    }
}
