<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\RekapCommandCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RekapCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_unduhan_menyimpan_agregat_yang_disetujui_dan_pengajuan_ulang_tidak_menduplikasi(): void
    {
        $this->travelTo(Carbon::parse('2026-11-23 00:30:00', 'Asia/Jakarta'));
        $pakis = Desa::create(['nama' => 'Mangliawan']);
        Desa::create(['nama' => 'Sekarpuro']);
        $luar = Desa::create(['nama' => 'Sawojajar', 'kecamatan' => 'Kedungkandang']);
        $disnaker = User::factory()->create(['role' => User::ROLE_DISNAKER, 'name' => 'Penyetuju internal uji']);
        $pemilik = User::factory()->create(['role' => User::ROLE_USAHA]);
        $usaha = $pemilik->usaha()->create(['nama_usaha' => 'Usaha rahasia uji', 'desa_id' => $pakis->id, 'status_verifikasi' => 'terverifikasi']);
        $lowongan = $usaha->lowongan()->create(['judul' => 'Posisi uji', 'kuota' => 2, 'tanggal_tutup' => '2026-11-23']);
        $warga = User::factory()->create(['role' => User::ROLE_PENCARI, 'desa_id' => $pakis->id, 'name' => 'Identitas warga uji']);
        $profil = $warga->profilPencari()->create(['persetujuan_data_at' => now()]);
        $profil->lamaran()->create(['lowongan_id' => $lowongan->id, 'status' => 'diterima'])->penempatan()->create([]);
        User::factory()->create(['role' => User::ROLE_PENCARI, 'desa_id' => $pakis->id])->profilPencari()->create();
        User::factory()->create(['role' => User::ROLE_PENCARI, 'desa_id' => $luar->id])
            ->profilPencari()->create(['persetujuan_data_at' => now()]);

        $halaman = route('disnaker.command-center.index');
        $this->actingAs($disnaker)->get(route('disnaker.dasbor'))->assertOk()->assertSee($halaman);
        $pratinjau = $this->get($halaman)->assertOk()->assertSee('Tinjau rekap Kecamatan Pakis')
            ->assertSee('Setujui rekap')->assertSee('Mangliawan')->assertSee('Sekarpuro')
            ->assertDontSee('Sawojajar')->assertDontSee('Unduh JSON')->assertDontSee('Identitas warga uji');
        $form = ['sidik_data' => $pratinjau->viewData('sidikData')];
        $this->post(route('disnaker.command-center.store'), $form)->assertRedirect($halaman);
        $this->post(route('disnaker.command-center.store'), $form)->assertRedirect($halaman);
        $this->assertDatabaseCount('rekap_command_center', 1);
        $rekap = RekapCommandCenter::sole();
        $this->assertSame($disnaker->id, $rekap->disetujui_oleh);
        $this->assertDatabaseCount('log_akses', 1);
        $this->assertDatabaseHas('log_akses', [
            'user_id' => $disnaker->id, 'aksi' => 'setujui_rekap_command_center', 'objek' => 'rekap_command_center#'.$rekap->id,
        ]);

        $unduh = route('disnaker.command-center.download', $rekap);
        $this->get($halaman)->assertOk()->assertSee($unduh)->assertSee('Disetujui 23/11/2026 00:30:00 WIB');
        $hasil = $this->get($unduh)->assertOk()
            ->assertDownload('rekap-pakis-'.$rekap->id.'.json')
            ->assertExactJson([
                'sumber' => 'Lokerin',
                'kecamatan' => 'Pakis',
                'tanggal_rekap_wib' => '2026-11-23',
                'per_desa' => [
                    ['desa' => 'Mangliawan', 'pencari' => 1, 'lowongan' => 1, 'lamaran' => 1, 'penempatan' => 1],
                    ['desa' => 'Sekarpuro', 'pencari' => 0, 'lowongan' => 0, 'lamaran' => 0, 'penempatan' => 0],
                ],
                'total' => ['pencari' => 1, 'lowongan' => 1, 'lamaran' => 1, 'penempatan' => 1],
                'disetujui_pada' => '2026-11-23T00:30:00+07:00',
            ]);
        $this->assertStringContainsString('no-store', $hasil->headers->get('Cache-Control'));

        $profil->update(['persetujuan_data_at' => null]);
        $this->post($halaman, $form)->assertRedirect($halaman)->assertSessionHasErrors('sidik_data');
        $baru = $this->get($halaman)->assertOk()->viewData('sidikData');
        $this->assertNotSame($form['sidik_data'], $baru);
        $this->post($halaman, ['sidik_data' => $baru, 'data' => ['pencari' => 999]])->assertRedirect($halaman);
        $this->assertDatabaseCount('rekap_command_center', 2);
        $this->get($unduh)->assertExactJson($hasil->json());
        $this->get(route('disnaker.command-center.download', RekapCommandCenter::latest('id')->firstOrFail()))
            ->assertJsonPath('total.pencari', 0)->assertJsonPath('total.lamaran', 0)->assertJsonPath('total.penempatan', 0);
    }

    public function test_hanya_disnaker_bisa_meninjau_menyetujui_dan_mengunduh(): void
    {
        Desa::create(['nama' => 'Sekarpuro']);
        $disnaker = User::factory()->create(['role' => User::ROLE_DISNAKER]);
        $halaman = route('disnaker.command-center.index');
        $this->get($halaman)->assertRedirect(route('login'));
        $sidikData = $this->actingAs($disnaker)->get($halaman)->assertOk()->viewData('sidikData');
        $form = ['sidik_data' => $sidikData];
        $this->post($halaman, $form)->assertRedirect($halaman);
        $unduh = route('disnaker.command-center.download', RekapCommandCenter::sole());

        $this->get(route('disnaker.command-center.download', 9999))->assertNotFound();
        foreach ([User::ROLE_PENCARI, User::ROLE_USAHA, User::ROLE_KADER] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get($halaman)->assertForbidden();
            $this->post($halaman, $form)->assertForbidden();
            $this->get($unduh)->assertForbidden();
        }
    }

    public function test_rekap_tanpa_data_desa_tidak_bisa_disetujui(): void
    {
        $disnaker = User::factory()->create(['role' => User::ROLE_DISNAKER]);
        $halaman = route('disnaker.command-center.index');
        $pratinjau = $this->actingAs($disnaker)->get($halaman)->assertOk()->assertDontSee('Setujui rekap');
        $this->post($halaman, ['sidik_data' => $pratinjau->viewData('sidikData')])
            ->assertRedirect($halaman)->assertSessionHasErrors('sidik_data');
        $this->assertDatabaseCount('rekap_command_center', 0);
    }
}
