<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Lokerin
|--------------------------------------------------------------------------
| Semua angka di sini mengikuti Rancangan Lokerin Bab 4.3.
| Ubah di sini saja kalau kelompok mau menyesuaikan bobot atau ambang.
*/

return [

    // Bobot skor kecocokan (total 100)
    'bobot' => [
        'jurusan'    => 40, // J
        'keahlian'   => 30, // K
        'lokasi'     => 20, // D
        'pendidikan' => 10, // E
    ],

    // Lowongan dengan skor >= ambang tampil di rekomendasi
    'ambang_rekomendasi' => 50,

    // Nilai komponen lokasi (D)
    'lokasi' => [
        'desa_sama'      => 1.0,
        'kecamatan_sama' => 0.6,
        'luar'           => 0.2,
    ],

    // Urutan jenjang pendidikan untuk komponen E
    'level_pendidikan' => [
        'SD' => 1, 'SMP' => 2, 'SMA' => 3, 'SMK' => 3, 'MA' => 3, 'D3' => 4, 'S1' => 5,
    ],

    // Rumpun jurusan: jurusan dalam satu rumpun dapat nilai J = 0,5
    'rumpun_jurusan' => [
        'teknologi_informasi' => ['TKJ', 'RPL', 'Teknik Informatika', 'Sistem Informasi'],
        'seni_desain'         => ['Multimedia', 'DKV', 'Desain Grafis'],
        'bisnis'              => ['Akuntansi', 'Administrasi Perkantoran', 'Pemasaran', 'Manajemen'],
        'pariwisata_boga'     => ['Tata Boga', 'Perhotelan', 'Usaha Perjalanan Wisata'],
        'teknik'              => ['Teknik Kendaraan Ringan', 'Teknik Sepeda Motor', 'Teknik Mesin', 'Teknik Listrik'],
        'kesehatan'           => ['Farmasi', 'Keperawatan', 'Analis Kesehatan'],
        'umum'                => ['IPA', 'IPS', 'Bahasa'],
    ],

    // Peran pengguna
    'roles' => ['pencari', 'usaha', 'kader', 'disnaker'],
];
