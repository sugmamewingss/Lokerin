<?php

namespace Database\Seeders;

use App\Models\Desa;
use Illuminate\Database\Seeder;

/**
 * 15 desa Kecamatan Pakis.
 * Sumber: BPS Kabupaten Malang, Kecamatan Pakis Dalam Angka 2026
 * (Tabel 1.1 luas, Tabel 1.2 jarak ke ibu kota kecamatan, Tabel 3.1 penduduk 2025).
 */
class DesaSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // nama, luas km2, penduduk 2025, jarak ke ibu kota kecamatan (km)
            ['Sekarpuro', 1.96, 12129, 5],
            ['Ampeldento', 2.24, 6023, 4],
            ['Sumberkradenan', 3.63, 7495, 2],
            ['Kedungrejo', 4.38, 6622, 6],
            ['Banjarejo', 3.73, 7510, 6],
            ['Pucangsongo', 2.43, 2980, 7],
            ['Sukoanyar', 3.47, 7652, 4],
            ['Sumberpasir', 3.20, 6945, 2],
            ['Pakiskembar', 3.31, 9379, 1],
            ['Pakisjajar', 5.04, 13012, 1],
            ['Bunutwetan', 3.25, 11422, 2],
            ['Asrikaton', 3.73, 16593, 2],
            ['Saptorenggo', 3.53, 16414, 2],
            ['Mangliawan', 3.30, 17674, 5],
            ['Tirtomoyo', 6.42, 12886, 8],
        ];

        foreach ($data as [$nama, $luas, $penduduk, $jarak]) {
            Desa::updateOrCreate(['nama' => $nama, 'kecamatan' => 'Pakis'], [
                'luas_km2' => $luas, 'penduduk' => $penduduk, 'jarak_ke_kecamatan_km' => $jarak,
            ]);
        }

        // Satu wilayah di luar Pakis untuk contoh lowongan luar kecamatan (komponen D = 0,2).
        Desa::updateOrCreate(['nama' => 'Sawojajar (Kota Malang)', 'kecamatan' => 'Kedungkandang']);
    }
}
