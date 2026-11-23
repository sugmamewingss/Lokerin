<?php

namespace Tests\Feature;

use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\Lowongan;
use App\Models\Pelatihan;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformasiBanjirTest extends TestCase
{
    use RefreshDatabase;

    public function test_lowongan_menampilkan_rujukan_dan_kejadian_sesuai_desa_usaha(): void
    {
        $mangliawan = $this->lowongan(Desa::create(['nama' => 'Mangliawan']));
        $ampeldento = $this->lowongan(Desa::create(['nama' => 'Ampeldento']));
        $kedungrejo = $this->lowongan(Desa::create(['nama' => 'Kedungrejo']));
        $sekarpuro = $this->lowongan(Desa::create(['nama' => 'Sekarpuro']));
        $luar = $this->lowongan(Desa::create(['nama' => 'Mangliawan', 'kecamatan' => 'Kedungkandang']));

        $this->get(route('lowongan.detail', $mangliawan))->assertOk()
            ->assertSee('Banjir tercantum sebagai ancaman di Desa Mangliawan, Kecamatan Pakis.')
            ->assertSeeInOrder(['28/02/2026', '14/03/2022'])
            ->assertSee('RKPD Kabupaten Malang 2027')
            ->assertSee('https://bnpb.go.id/berita/perkembangan-situasi-dan-penanganan-bencana-di-tanah-air-2-maret-2026')
            ->assertSee('https://inarisk.bnpb.go.id/webgis/');
        $this->get(route('lowongan.detail', $ampeldento))->assertOk()->assertSee('14/03/2022')
            ->assertDontSee('28/02/2026')->assertDontSee('24/01/2025');
        $this->get(route('lowongan.detail', $kedungrejo))->assertOk()->assertSee('24/01/2025')
            ->assertSee('Laporan BNPB, 26 Januari 2025')->assertDontSee('14/03/2022');
        $this->get(route('lowongan.detail', $sekarpuro))->assertOk()->assertSee('Banjir tercantum sebagai ancaman di Desa Sekarpuro')
            ->assertSee('Lokerin belum memuat laporan kejadian khusus desa ini.')->assertDontSee('Kejadian terdokumentasi');
        $this->get(route('lowongan.detail', $luar))->assertOk()
            ->assertSee('Rujukan banjir untuk Mangliawan belum tersedia di Lokerin.')->assertDontSee('RKPD Kabupaten Malang 2027');
    }

    public function test_disnaker_menentukan_desa_kegiatan_dan_pelatihan_lama_tetap_terbaca(): void
    {
        $desa = Desa::create(['nama' => 'Kedungrejo']);
        $desaLain = Desa::create(['nama' => 'Saptorenggo']);
        $disnaker = User::factory()->create(['role' => 'disnaker']);
        $keahlian = Keahlian::create(['nama' => 'Pelayanan wisata']);
        $data = [
            'keahlian_id' => $keahlian->id, 'judul' => 'Pelatihan lokasi uji',
            'penyelenggara' => 'LPK Uji', 'jenis_penyelenggara' => 'lpk', 'lokasi' => 'Balai latihan uji',
        ];

        $this->actingAs($disnaker)->get(route('disnaker.pelatihan.create'))->assertOk()->assertSee('Desa lokasi pelatihan')
            ->assertSee('Kedungrejo')->assertSee('Saptorenggo');
        $this->post(route('disnaker.pelatihan.store'), $data + ['desa_id' => 9999])->assertSessionHasErrors('desa_id');
        $this->assertDatabaseCount('pelatihan', 0);
        $this->post(route('disnaker.pelatihan.store'), $data)->assertRedirect(route('disnaker.pelatihan.index'));
        $pelatihan = Pelatihan::sole();
        $detail = route('pelatihan.show', $pelatihan);
        $ubah = route('disnaker.pelatihan.update', $pelatihan);
        $this->get($detail)->assertOk()->assertSee('Balai latihan uji')->assertSee('Desa lokasi pelatihan belum dicantumkan.')
            ->assertDontSee('Kejadian terdokumentasi');

        $this->put($ubah, $data + ['desa_id' => $desa->id])->assertRedirect(route('disnaker.pelatihan.index'));
        $this->assertDatabaseHas('pelatihan', ['id' => $pelatihan->id, 'desa_id' => $desa->id]);
        $this->get($detail)->assertOk()->assertSee('Banjir tercantum sebagai ancaman di Desa Kedungrejo')->assertSee('24/01/2025');
        $this->put($ubah, $data + ['desa_id' => $desaLain->id])->assertRedirect(route('disnaker.pelatihan.index'));
        $this->get($detail)->assertOk()->assertSee('Banjir tercantum sebagai ancaman di Desa Saptorenggo')
            ->assertSee('28/02/2026')->assertDontSee('24/01/2025');

        $desaLain->delete();
        $this->assertNull($pelatihan->fresh()->desa_id);
        $this->get($detail)->assertOk()->assertSee('Balai latihan uji')->assertSee('Desa lokasi pelatihan belum dicantumkan.');
    }

    public function test_rujukan_ancaman_mencakup_lima_belas_desa_pakis(): void
    {
        $this->seed(DesaSeeder::class);
        $desaPakis = Desa::where('kecamatan', 'Pakis')->get();

        $this->assertCount(15, $desaPakis);
        $this->assertTrue($desaPakis->every(fn (Desa $desa) => $desa->informasiBanjir() !== null));
    }

    private function lowongan(Desa $desa): Lowongan
    {
        $pemilik = User::factory()->create(['role' => 'usaha', 'desa_id' => $desa->id]);
        $usaha = $pemilik->usaha()->create(['nama_usaha' => 'Usaha uji', 'desa_id' => $desa->id, 'status_verifikasi' => 'terverifikasi']);

        return $usaha->lowongan()->create(['judul' => 'Lowongan lokasi uji']);
    }
}
