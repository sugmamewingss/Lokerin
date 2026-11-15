<?php

namespace Database\Seeders;

use App\Models\Keahlian;
use Illuminate\Database\Seeder;

class KeahlianSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Digital dan kantor' => ['Microsoft Excel', 'Microsoft Word', 'Desain dengan Canva', 'Pemasaran media sosial', 'Photoshop', 'CorelDraw'],
            'Teknik' => ['Perbaikan komputer', 'Konfigurasi router', 'Servis sepeda motor', 'Servis HP', 'Instalasi listrik'],
            'Layanan' => ['Komunikasi', 'Pelayanan pelanggan', 'Kasir', 'Bahasa Inggris dasar'],
            'Boga dan wisata' => ['Memasak', 'Membuat kopi', 'Pemandu wisata', 'Housekeeping'],
            'Umum' => ['Mengemudi mobil (SIM A)', 'Mengemudi motor (SIM C)', 'Budidaya tanaman', 'Gudang dan pengemasan'],
        ];

        foreach ($data as $kategori => $daftar) {
            foreach ($daftar as $nama) {
                Keahlian::updateOrCreate(['nama' => $nama], ['kategori' => $kategori]);
            }
        }
    }
}
