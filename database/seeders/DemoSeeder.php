<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\Lamaran;
use App\Models\Lowongan;
use App\Models\Penempatan;
use App\Models\ProfilPencari;
use App\Models\Usaha;
use App\Models\User;
use App\Services\MatchingService;
use Illuminate\Database\Seeder;

/**
 * Data CONTOH untuk demo presentasi (Rancangan Bab 5.4 dan 8.3).
 * Semua nama orang dan usaha di sini FIKTIF.
 * Semua akun memakai kata sandi: password
 */
class DemoSeeder extends Seeder
{
    private array $desa;
    private array $keahlian;

    public function run(): void
    {
        fake()->seed(2026);
        $this->desa = Desa::pluck('id', 'nama')->all();
        $this->keahlian = Keahlian::pluck('id', 'nama')->all();

        $this->akunPetugas();
        $this->pencariKerja();
        $this->usahaDanLowongan();
        $this->lamaranContoh();
    }

    private function akunPetugas(): void
    {
        User::updateOrCreate(['email' => 'disnaker@lokerin.test'], [
            'name' => 'Admin Disnaker', 'no_hp' => '080000000001', 'password' => 'password', 'role' => User::ROLE_DISNAKER,
        ]);

        foreach (['Mangliawan', 'Saptorenggo', 'Sekarpuro', 'Pakiskembar', 'Pucangsongo'] as $i => $nama) {
            User::updateOrCreate(['email' => 'kader.'.strtolower($nama).'@lokerin.test'], [
                'name' => "Kader $nama", 'no_hp' => '08000000001'.$i, 'password' => 'password',
                'role' => User::ROLE_KADER, 'desa_id' => $this->desa[$nama],
            ]);
        }
    }

    private function pencariKerja(): void
    {
        // Tokoh demo: Rizky (Rancangan Tabel 4.3)
        $this->buatPencari('Rizky Pratama', 'rizky@lokerin.test', '081200000001', 'Sekarpuro', 'SMK', 'TKJ',
            ['Perbaikan komputer', 'Microsoft Excel', 'Komunikasi']);

        $jurusan = [
            ['SMK', 'TKJ'], ['SMK', 'RPL'], ['SMK', 'Akuntansi'], ['SMK', 'Tata Boga'], ['SMK', 'Teknik Sepeda Motor'],
            ['SMK', 'Administrasi Perkantoran'], ['SMA', 'IPS'], ['SMA', 'IPA'], ['MA', 'IPS'], ['SMP', null],
        ];
        $desaKajian = ['Mangliawan', 'Mangliawan', 'Saptorenggo', 'Saptorenggo', 'Sekarpuro', 'Sekarpuro', 'Pakiskembar', 'Pucangsongo'];
        $semuaKeahlian = array_keys($this->keahlian);

        for ($i = 2; $i <= 30; $i++) {
            [$pend, $jur] = fake()->randomElement($jurusan);
            $this->buatPencari(
                fake()->name(), "pencari$i@lokerin.test", sprintf('0812000000%02d', $i),
                fake()->randomElement($desaKajian), $pend, $jur,
                fake()->randomElements($semuaKeahlian, fake()->numberBetween(1, 4))
            );
        }
    }

    private function buatPencari(string $nama, string $email, string $hp, string $desa, string $pend, ?string $jur, array $keahlian): void
    {
        $user = User::updateOrCreate(['email' => $email], [
            'name' => $nama, 'no_hp' => $hp, 'password' => 'password', 'role' => User::ROLE_PENCARI, 'desa_id' => $this->desa[$desa],
        ]);
        $profil = ProfilPencari::updateOrCreate(['user_id' => $user->id], [
            'tanggal_lahir' => fake()->dateTimeBetween('-25 years', '-17 years'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'pendidikan_terakhir' => $pend, 'jurusan' => $jur, 'status_kerja' => 'belum_bekerja',
            'bersedia_paruh_waktu' => true, 'persetujuan_data_at' => now(),
        ]);
        $profil->keahlian()->sync(array_map(fn ($k) => $this->keahlian[$k], $keahlian));
    }

    private function usahaDanLowongan(): void
    {
        $usaha = [
            // nama usaha, jenis, desa, status verifikasi, [lowongan...]
            ['Kedai Sumber Rasa', 'Kuliner', 'Mangliawan', 'menunggu', []], // diverifikasi saat demo (langkah 1)
            ['Warung Wisata Wendit', 'Kuliner', 'Mangliawan', 'terverifikasi', [
                ['Admin dan Kasir', null, 'SMA', 'penuh_waktu', ['Microsoft Excel', 'Komunikasi', 'Kasir'], 2100000],
                ['Juru Masak', 'Tata Boga', 'SMP', 'penuh_waktu', ['Memasak'], 2000000],
            ]],
            ['Jasa Wisata Mangliawan', 'Pariwisata', 'Mangliawan', 'terverifikasi', [
                ['Pemandu Wisata Akhir Pekan', null, 'SMA', 'paruh_waktu', ['Pemandu wisata', 'Komunikasi'], 75000],
            ]],
            ['Toko Bangunan Saptorenggo Jaya', 'Ritel', 'Saptorenggo', 'terverifikasi', [
                ['Admin Gudang', 'Administrasi Perkantoran', 'SMA', 'penuh_waktu', ['Microsoft Excel', 'Gudang dan pengemasan'], 2200000],
                ['Sopir Pengantaran', null, 'SMP', 'penuh_waktu', ['Mengemudi mobil (SIM A)'], 2300000],
            ]],
            ['Mart Saptorenggo', 'Ritel', 'Saptorenggo', 'terverifikasi', [
                ['Kasir Shift Malam', null, 'SMA', 'paruh_waktu', ['Kasir', 'Pelayanan pelanggan'], 1500000],
            ]],
            ['NetDesa Sekarpuro', 'Jasa internet', 'Sekarpuro', 'terverifikasi', [
                ['Teknisi Jaringan', 'TKJ', 'SMA', 'penuh_waktu', ['Konfigurasi router', 'Perbaikan komputer'], 2500000],
            ]],
            ['Bengkel Motor Sekarpuro', 'Bengkel', 'Sekarpuro', 'terverifikasi', [
                ['Mekanik Motor', 'Teknik Sepeda Motor', 'SMK', 'penuh_waktu', ['Servis sepeda motor'], 2200000],
            ]],
            ['Fotokopi dan ATK Pakiskembar', 'Ritel dan jasa', 'Pakiskembar', 'terverifikasi', [
                ['Operator Fotokopi dan Desain', null, 'SMA', 'penuh_waktu', ['Desain dengan Canva', 'Microsoft Word'], 1900000],
            ]],
            ['Kebun Sayur Pucangsongo', 'Pertanian', 'Pucangsongo', 'terverifikasi', [
                ['Pekerja Kebun Harian', null, 'SD', 'harian', ['Budidaya tanaman'], 80000],
            ]],
            ['Kopi Pakisjajar', 'Kuliner', 'Pakisjajar', 'terverifikasi', [
                ['Barista', null, 'SMA', 'paruh_waktu', ['Membuat kopi', 'Pelayanan pelanggan'], 1600000],
            ]],
            ['Percetakan Sawojajar', 'Percetakan', 'Sawojajar (Kota Malang)', 'terverifikasi', [
                ['Desainer Grafis', 'DKV', 'SMA', 'penuh_waktu', ['Photoshop', 'CorelDraw'], 2800000],
            ]],
        ];

        foreach ($usaha as $i => [$nama, $jenis, $desa, $status, $daftarLowongan]) {
            $pemilik = User::updateOrCreate(['email' => 'usaha'.($i + 1).'@lokerin.test'], [
                'name' => "Pemilik $nama", 'no_hp' => sprintf('0813000000%02d', $i + 1), 'password' => 'password',
                'role' => User::ROLE_USAHA, 'desa_id' => $this->desa[$desa],
            ]);
            $u = Usaha::updateOrCreate(['nama_usaha' => $nama], [
                'user_id' => $pemilik->id, 'jenis_usaha' => $jenis, 'desa_id' => $this->desa[$desa],
                'alamat' => "Desa $desa", 'status_verifikasi' => $status,
                'diverifikasi_at' => $status === 'terverifikasi' ? now() : null,
            ]);

            foreach ($daftarLowongan as [$judul, $jur, $pend, $jenisKerja, $wajib, $gaji]) {
                $l = Lowongan::updateOrCreate(['usaha_id' => $u->id, 'judul' => $judul], [
                    'deskripsi' => "Dibutuhkan $judul untuk $nama di Desa $desa. (Data contoh untuk demo.)",
                    'jenis_kerja' => $jenisKerja, 'pendidikan_min' => $pend, 'jurusan_target' => $jur,
                    'kuota' => fake()->numberBetween(1, 3), 'gaji_min' => $gaji, 'status' => 'aktif',
                    'tanggal_tutup' => now()->addMonth(),
                ]);
                $l->keahlian()->sync(collect($wajib)->mapWithKeys(fn ($k) => [$this->keahlian[$k] => ['wajib' => true]])->all());
            }
        }
    }

    /** Beberapa lamaran dan penempatan supaya dasbor tidak kosong. Rizky sengaja dikosongkan untuk demo. */
    private function lamaranContoh(): void
    {
        $matching = app(MatchingService::class);
        $profil = ProfilPencari::whereHas('user', fn ($q) => $q->where('email', '!=', 'rizky@lokerin.test'))->get();

        foreach ($profil as $p) {
            $rek = $matching->rekomendasi($p)->take(2);
            foreach ($rek as $idx => $r) {
                $status = fake()->randomElement(['dikirim', 'dilihat', 'wawancara', 'diterima', 'ditolak']);
                $lamaran = Lamaran::updateOrCreate(
                    ['lowongan_id' => $r['lowongan']->id, 'profil_id' => $p->id],
                    ['skor_cocok' => $r['hasil']['skor'], 'status' => $status, 'dikirim_at' => now()->subDays(fake()->numberBetween(0, 20))]
                );
                if ($status === 'diterima') {
                    Penempatan::updateOrCreate(['lamaran_id' => $lamaran->id], [
                        'tanggal_mulai' => now()->subDays(fake()->numberBetween(0, 10)), 'jenis_kontrak' => 'kontrak',
                    ]);
                }
            }
        }
    }
}
