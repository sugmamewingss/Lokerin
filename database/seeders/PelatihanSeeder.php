<?php

namespace Database\Seeders;

use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\Pelatihan;
use Illuminate\Database\Seeder;

/** Data pelatihan CONTOH (fiktif) untuk demo saran pelatihan (F-12). */
class PelatihanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Kasir', 'Pelatihan Kasir dan Aplikasi POS', 'lpk', 'LPK Contoh Pakis', 'Sabtu, 2 minggu', 'Balai Desa Pakiskembar', 'Pakiskembar'],
            ['Konfigurasi router', 'Dasar Jaringan dan Konfigurasi Router', 'smk', 'SMK Mitra (contoh)', 'Minggu, 4 pertemuan', 'Lab SMK Mitra', null],
            ['Pelayanan pelanggan', 'Pelayanan Prima untuk Usaha Wisata', 'lainnya', 'Disparbud (contoh)', 'Bulan depan, 3 hari', 'Kawasan Wendit', 'Mangliawan'],
            ['Pemasaran media sosial', 'Jualan Online untuk UMKM', 'lainnya', 'Diskominfo (contoh)', 'Daring, 2 sesi', 'Zoom', null],
            ['Membuat kopi', 'Barista Dasar', 'lpk', 'LPK Contoh Pakis', 'Sabtu-Minggu', 'Pakisjajar', 'Pakisjajar'],
            ['Microsoft Excel', 'Excel untuk Administrasi', 'blk', 'BLK Mitra (contoh)', 'Malam hari, 4 sesi', 'Balai Desa Sekarpuro', 'Sekarpuro'],
        ];

        $desa = Desa::where('kecamatan', 'Pakis')->pluck('id', 'nama');
        foreach ($data as [$keahlian, $judul, $jenis, $penyelenggara, $jadwal, $lokasi, $namaDesa]) {
            Pelatihan::withTrashed()->updateOrCreate(['judul' => $judul], [
                'keahlian_id' => Keahlian::where('nama', $keahlian)->value('id'),
                'jenis_penyelenggara' => $jenis, 'penyelenggara' => $penyelenggara, 'jadwal' => $jadwal, 'lokasi' => $lokasi,
                'desa_id' => $desa[$namaDesa] ?? null, 'kuota' => 20,
            ]);
        }
    }
}
