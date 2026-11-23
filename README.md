# Lokerin

Platform lowongan kerja lokal untuk warga Kecamatan Pakis, Kabupaten Malang.
Prototipe tugas mata kuliah Smart City 2026. Rancangan lengkap ada di dokumen **Rancangan_Lokerin.docx**.

Lokerin mempertemukan pencari kerja (terutama lulusan SMK/SMA) dengan lowongan dari UMKM dan usaha di sekitar Pakis. Rekomendasi lowongan memakai skor kecocokan yang bisa dijelaskan:

```
Skor = 40 x Jurusan + 30 x Keahlian + 20 x Lokasi + 10 x Pendidikan
```

Lowongan dengan skor 50 ke atas tampil di rekomendasi. Bobot dan ambang bisa diubah di `config/lokerin.php`.

## Teknologi

- Laravel 11 (PHP 8.2+), Blade, Tailwind CSS 3 (+ plugin forms), Vite
- SQLite untuk pengembangan (bisa diganti MySQL)
- Tanpa paket tambahan selain bawaan Laravel

## Menjalankan di laptop

Butuh: PHP 8.2+, Composer, Node.js 18+. Bisa pakai Laragon/XAMPP atau instalasi biasa.

```bash
composer install
cp .env.example .env          # Windows CMD: copy .env.example .env
php artisan key:generate
php artisan migrate --seed    # jawab "yes" saat ditanya membuat database.sqlite
npm install
npm run dev                   # biarkan jalan di terminal ini
```

Di terminal kedua:

```bash
php artisan serve
```

Buka http://localhost:8000

Mengulang data dari awal: `php artisan migrate:fresh --seed`

### Pakai MySQL (opsional)

Buat database `lokerin`, lalu ubah `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lokerin
DB_USERNAME=root
DB_PASSWORD=
```

## Akun demo

Semua kata sandi: `password`. Semua nama orang dan usaha adalah **fiktif**.

| Peran | Login | Keterangan |
|---|---|---|
| Pencari kerja | rizky@lokerin.test | Tokoh demo: SMK TKJ dari Sekarpuro, belum punya lamaran |
| Pencari kerja | pencari2@lokerin.test s.d. pencari30@lokerin.test | Data acak |
| Usaha | usaha1@lokerin.test | Kedai Sumber Rasa (Mangliawan), status **menunggu verifikasi** |
| Usaha | usaha2@lokerin.test s.d. usaha11@lokerin.test | Usaha terverifikasi dengan lowongan |
| Kader desa | kader.mangliawan@lokerin.test | Juga: kader.saptorenggo, kader.sekarpuro, kader.pakiskembar, kader.pucangsongo |
| Disnaker | disnaker@lokerin.test | Dasbor kecamatan |

Login bisa menggunakan email atau nomor HP.

Data 15 desa (luas, penduduk 2025, jarak ke ibu kota kecamatan) diambil dari BPS Kabupaten Malang, *Kecamatan Pakis Dalam Angka 2026*.

## Yang sudah jalan

- Daftar, masuk, keluar; 4 peran dengan pembatasan halaman (`role` middleware)
- Halaman publik: beranda, daftar lowongan dengan filter, detail lowongan
- Pencari kerja: profil + keahlian + persetujuan data, rekomendasi berskor, lamar, daftar lamaran, saran pelatihan di detail lowongan
- Kader desa: dasbor angka desa, verifikasi usaha di desanya (dengan log audit)
- Disnaker: rekap per desa
- Usaha: beranda sederhana (daftar lowongan dan jumlah pelamar)
- Mesin skor (`app/Services/SkorKecocokan.php`) + unit test yang mencocokkan contoh di rancangan (skor 85, 82, 14)

## Yang perlu dikerjakan (lihat komentar `TODO` di kode)

| Kode | Fitur | Penanggung jawab | File awal |
|---|---|---|---|
| F-04 | Profil usaha, pasang/ubah/tutup lowongan | Adefriko | `Usaha/BerandaController.php`, `routes/web.php` |
| F-09, F-10 | Daftar pelamar urut skor, ubah status, buat penempatan saat diterima | Adefriko | pakai `Lamaran::bolehPindahKe()` dan `kontakTerbuka()` |
| F-11 | Grafik dasbor desa dan kecamatan (Chart.js) | Muflih | `Kader/DasborController.php`, `Disnaker/DasborController.php` |
| F-13 | Kader mendaftarkan warga | Rosihan / Rakapaksi | isi `didaftarkan_oleh` di `profil_pencari` |
| F-15 | Kelola pelatihan dan keahlian (Disnaker) | Muflih | |
| UI | Rapikan tampilan mobile, batalkan lamaran, halaman pelatihan | Rakapaksi | `resources/views/` |
| Privasi | Cek semua halaman sesuai UU PDP, halaman kebijakan data | Rosihan | |
| Inti | Penyempurnaan matching, review dan merge PR | Alfi | `app/Services/` |

## Struktur penting

```
app/
  Http/Controllers/
    Auth/            masuk dan daftar
    Pencari/         beranda rekomendasi, profil, lamaran
    Usaha/           beranda usaha (TODO lowongan dan pelamar)
    Kader/           dasbor desa, verifikasi usaha
    Disnaker/        dasbor kecamatan
    PublikController.php
  Http/Middleware/CekRole.php
  Models/            Desa, User, ProfilPencari, Keahlian, Usaha, Lowongan, Lamaran, Penempatan, Pelatihan, LogAkses
  Services/
    SkorKecocokan.php   rumus skor (tanpa database, mudah diuji)
    MatchingService.php penghubung rumus dengan model + saran pelatihan
config/lokerin.php      bobot, ambang, rumpun jurusan, level pendidikan
database/
  migrations/2026_11_16_*  12 tabel sesuai ERD rancangan
  seeders/                 desa (BPS), keahlian, pelatihan, data demo
resources/views/           Blade + Tailwind
tests/
  Unit/SkorKecocokanTest.php
  Feature/AlurDemoTest.php skenario demo
```

## Menjalankan test

```bash
php artisan test
```

## Alur kerja Git

1. Jangan commit langsung ke `main`.
2. Buat branch per fitur: `git checkout -b fitur/lowongan-usaha`
3. Commit kecil dan jelas: `git commit -m "Tambah form pasang lowongan"`
4. Push lalu buat Pull Request ke `main`; minta satu anggota lain mereview.
5. Sebelum mulai kerja tiap hari: `git checkout main && git pull`, lalu `git merge main` di branch kamu.

Jangan pernah commit file `.env`, folder `vendor/`, atau `node_modules/` (sudah diatur di `.gitignore`).

## Skenario demo (5 menit)

1. Login `kader.mangliawan@lokerin.test` → Verifikasi → verifikasi **Kedai Sumber Rasa**.
2. Login `usaha1@lokerin.test` → pasang lowongan Admin dan Kasir *(menunggu fitur F-04)*.
3. Login `rizky@lokerin.test` → Rekomendasi: Teknisi Jaringan (85%) di atas Admin dan Kasir (82%) → buka detail → lihat saran pelatihan → Lamar.
4. Login sebagai usaha → ubah status Rizky ke wawancara lalu diterima *(menunggu fitur F-09)*.
5. Login kader/DisNaKer → angka penempatan bertambah.
