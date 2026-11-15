<?php

namespace Database\Seeders;

use App\Models\Keahlian;
use App\Models\Pelatihan;
use Illuminate\Database\Seeder;

/** Data pelatihan CONTOH (fiktif) untuk demo saran pelatihan (F-12). */
class PelatihanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Kasir', 'Pelatihan Kasir dan Aplikasi POS', 'LPK Contoh Pakis', 'Sabtu, 2 minggu', 'Balai Desa Pakiskembar'],
            ['Konfigurasi router', 'Dasar Jaringan dan Konfigurasi Router', 'SMK Mitra (contoh)', 'Minggu, 4 pertemuan', 'Lab SMK Mitra'],
            ['Pelayanan pelanggan', 'Pelayanan Prima untuk Usaha Wisata', 'Disparbud (contoh)', 'Bulan depan, 3 hari', 'Kawasan Wendit'],
            ['Pemasaran media sosial', 'Jualan Online untuk UMKM', 'Diskominfo (contoh)', 'Daring, 2 sesi', 'Zoom'],
            ['Membuat kopi', 'Barista Dasar', 'LPK Contoh Pakis', 'Sabtu-Minggu', 'Pakisjajar'],
            ['Microsoft Excel', 'Excel untuk Administrasi', 'Karang Taruna (contoh)', 'Malam hari, 4 sesi', 'Balai Desa Sekarpuro'],
        ];

        foreach ($data as [$keahlian, $judul, $penyelenggara, $jadwal, $lokasi]) {
            Pelatihan::updateOrCreate(['judul' => $judul], [
                'keahlian_id' => Keahlian::where('nama', $keahlian)->value('id'),
                'penyelenggara' => $penyelenggara, 'jadwal' => $jadwal, 'lokasi' => $lokasi, 'kuota' => 20,
            ]);
        }
    }
}
