# Lokerin

Platform lowongan kerja lokal untuk warga Kecamatan Pakis, Kabupaten Malang.
Prototipe tugas mata kuliah Smart City 2026. Rancangan lengkap ada di dokumen **Rancangan_Lokerin.docx**.

Lokerin mempertemukan pencari kerja (terutama lulusan SMK/SMA) dengan lowongan dari UMKM dan usaha di sekitar Pakis. Rekomendasi lowongan memakai skor kecocokan yang bisa dijelaskan:

```
Skor = 40 x Jurusan + 30 x Keahlian + 20 x Lokasi + 10 x Pendidikan
```

Lowongan dengan skor 50 ke atas tampil di rekomendasi. Bobot dan ambang bisa diubah di `config/lokerin.php`.
Dalam urutan **Paling cocok**, jarak memecah skor yang sama. Urutan **Terdekat** mendahulukan jarak dari desa asal di antara lowongan yang lolos ambang skor.

Jarak merupakan perkiraan garis lurus antartitik pusat wilayah. Data mencakup 15 desa Pakis dan Sawojajar untuk contoh lowongan luar kecamatan. Aplikasi menyimpan titik dari poligon administrasi sehingga rekomendasi dapat berjalan tanpa mengambil data peta saat dibuka. [Geoportal Kementerian Pertanian](https://geoportal.pertanian.go.id/arcgis/rest/services/Hosted/Batas_Administrasi_Desa/FeatureServer/0)

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
- Pencari kerja bisa mengisi profil dan keahlian, mengelola persetujuan data, melihat rekomendasi dan saran pelatihan, serta membatalkan lamaran sebelum wawancara
- Kader desa: dasbor angka desa, verifikasi usaha di desanya (dengan log audit)
- Kader dapat mendampingi pendaftaran warga di desanya. Warga menerima tautan aktivasi sekali pakai untuk membuat kata sandi dan memberi persetujuan penggunaan profil lewat akun sendiri
- Kader bisa menyiapkan daftar lowongan publik untuk papan desa dan menyalin ringkasan untuk grup WhatsApp RW
- Dasbor kader menampilkan rekap desa dan grafik jurusan. Disnaker melihat rekap seluruh desa Pakis. Kader Mangliawan dan Disnaker bisa membaca indikator uji coba dengan periode enam bulan pilihan
- Setelah profil diverifikasi kader, usaha bisa menerbitkan lowongan termasuk paruh waktu. Usaha juga bisa membuat draf, menutup lowongan, dan melihat jumlah pelamar
- Usaha terverifikasi bisa membuka pelamar per lowongan, melihat urutan skor kecocokan, memproses status, dan mencatat tanggal mulai, kontrak, serta kepesertaan BPJS saat menerima pelamar. Daftar publik menampilkan lowongan yang masih memiliki kuota penempatan
- Kebijakan data publik yang menjelaskan penggunaan data dan batas akses per peran
- Disnaker bisa mengelola keahlian dan katalog pelatihan BLK, LPK, SMK, serta mitra lain. Detail pelatihan menaut ke lowongan terbuka yang mewajibkan keahlian tersebut. Pencari kerja dengan persetujuan aktif melihat saran sesuai keahlian wajib yang belum dimiliki
- Detail lowongan dan pelatihan menampilkan rujukan banjir tingkat desa dan kejadian terdokumentasi yang sesuai lokasi
- Warga dan kader Pakiskembar dapat mencatat serta melacak permohonan pada simulasi pendampingan AK/1
- Mesin skor (`app/Services/SkorKecocokan.php`) + unit test yang mencocokkan contoh di rancangan (skor 85, 82, 14)

## Yang perlu dikerjakan (lihat komentar `TODO` di kode)

| Kode | Fitur | Penanggung jawab | File awal |
|---|---|---|---|
| UI | Rapikan tampilan mobile | Rakapaksi | `resources/views/` |
| Privasi | Tinjau kebijakan dan SOP pengelolaan data sebelum uji coba dengan data warga | Rosihan | |

## Struktur penting

```
app/
  Http/Controllers/
    Auth/            masuk dan daftar
    Pencari/         beranda rekomendasi, profil, lamaran
    Usaha/           profil, lowongan, dan proses pelamar
    Kader/           dasbor desa, verifikasi usaha, dan pendaftaran warga
    Disnaker/        dasbor kecamatan, katalog pelatihan, dan keahlian
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
2. Login `usaha1@lokerin.test` → Usaha saya → Buat lowongan → pilih jenis **Paruh waktu** dan status **Aktif**. Buka daftar lowongan publik dan filter **Paruh waktu** untuk melihat hasilnya.
3. Login `rizky@lokerin.test` → Rekomendasi: Teknisi Jaringan (85%) di atas Admin dan Kasir (82%) → buka detail → lihat saran pelatihan → Lamar.
4. Login `usaha6@lokerin.test` → Teknisi Jaringan → **Pelamar**. Pada lamaran Rizky, pilih **Tandai sudah dilihat**, **Panggil wawancara**, lalu **Terima dan catat penempatan**. Isi tanggal mulai, jenis kontrak, dan status BPJS untuk menyimpan penerimaan.
5. Login lagi sebagai Rizky dan buka **Lamaran** untuk melihat status serta ringkasan penempatan. Di dasbor `kader.sekarpuro@lokerin.test` atau `disnaker@lokerin.test`, rekap penempatan Sekarpuro bertambah.

### Demo katalog pelatihan

1. Login `disnaker@lokerin.test` → **Kelola pelatihan** → **Tambah pelatihan**. Pilih keahlian **Konfigurasi router** yang diwajibkan lowongan **Teknisi Jaringan**.
2. Sebelum kuota lowongan itu penuh, buka **Pelatihan** dari menu publik, lalu buka detail pelatihan yang baru dibuat. Bagian lowongan terkait menampilkan **Teknisi Jaringan**.
3. Login `rizky@lokerin.test` dan buka detail **Teknisi Jaringan**. Pelatihan tersebut muncul karena Rizky belum memiliki keahlian konfigurasi router.
4. Arsipkan pelatihan dari halaman pengelolaan Disnaker. Pelatihan hilang dari katalog dan saran. Pilih **Pulihkan** untuk menampilkannya kembali.

### Demo pendaftaran warga oleh kader

1. Login `kader.mangliawan@lokerin.test` → **Daftarkan warga**. Isi nama dan nomor HP uji yang belum terdaftar, lalu pilih **Buat tautan aktivasi**.
2. Salin tautan dan buka di perangkat warga atau jendela privat. Buat kata sandi, lalu pilih **Aktifkan akun**.
3. Isi profil, pilih persetujuan data, dan simpan. Profil aktif masuk ke rekap desa. Warga selanjutnya bisa masuk dengan nomor HP dan kata sandinya sendiri.
4. Jika tautan kedaluwarsa, pilih **Buat tautan baru** pada daftar **Menunggu aktivasi**. Tautan lama berhenti berlaku.

### Indikator uji coba Mangliawan

Di dasbor kader Mangliawan atau Disnaker, pilih tanggal mulai untuk melihat enam bulan dalam WIB. Tampilan awal memakai semua catatan. Rekap desa mengikuti desa usaha untuk lowongan serta desa pencari untuk lamaran dan penempatan.

- Pendaftar dihitung berdasarkan tanggal persetujuan profil yang masih aktif. Tingkat penempatan membagi jumlah pendaftar unik dengan penempatan tercatat dalam periode dengan jumlah pendaftar aktif pada periode tersebut.
- Usaha mengisi skala UMKM di profil dan menunggu verifikasi kader. Setiap UMKM aktif yang memiliki lowongan dengan tanggal terbit tercatat dalam periode dihitung sekali. Lowongan lama memperoleh tanggal catatan saat pertama kali disimpan sebagai aktif setelah pemutakhiran. Catatan tersebut tidak diberi tanggal mundur.
- Target merujuk Tabel 29 Buku I. Realisasi TPT dan peserta pelatihan kader tetap berasal dari BPS/Sakernas dan laporan desa.

### Papan desa dan WhatsApp RW

Buka **Bagikan lowongan** dari dasbor kader. Pilih cakupan desa atau kecamatan, lalu gunakan **Cetak untuk papan desa** atau **Salin ringkasan**. Daftar cetak memuat seluruh lowongan aktif pada cakupan tersebut. Ringkasan WhatsApp memuat hingga 10 lowongan dengan tenggat terdekat, tautan publik, dan waktu daftar dibuat dalam WIB.

### Informasi banjir lokasi kerja dan pelatihan

Detail lowongan memakai desa pada profil usaha. Untuk pelatihan, Disnaker memilih **Desa lokasi pelatihan** pada formulir katalog. Data pelatihan lama tetap menyimpan keterangan tempat yang sudah ada dan desa dapat dilengkapi dari formulir. Pelatihan tanpa desa menampilkan status lokasi yang belum dicantumkan.

Rujukan mencakup daftar ancaman banjir untuk 15 desa Pakis dan beberapa kejadian yang dihimpun BNPB dari laporan lapangan. Tanggal pada riwayat adalah tanggal kejadian. Informasi berlaku pada tingkat desa, dan tautan InaRISK tersedia untuk melihat peta. [RKPD Kabupaten Malang 2027, hlm. 111](https://jdih.malangkab.go.id/dokumen/peraturan-bupati-malang-nomor-24-tahun-2026-tentang-rencana-kerja-pemerintah-daerah-tahun-2027/berkas/document) · [WebGIS InaRISK BNPB](https://inarisk.bnpb.go.id/webgis/)

### Demo pendampingan AK/1 Pakiskembar

1. Daftarkan akun fiktif sebagai pencari kerja dengan desa Pakiskembar, atau dampingi pendaftarannya melalui akun `kader.pakiskembar@lokerin.test` dan selesaikan aktivasi.
2. Warga membuka **AK/1**, memberi persetujuan, lalu memilih **Catat permohonan**. Untuk pengajuan berbantuan, kader membuka **Pendampingan AK/1** dari dasbor, mengisi nomor HP akun warga, dan mencatat bahwa warga setuju.
3. Kader membuka permohonan. **Minta kelengkapan** mengembalikan permohonan kepada warga atau kader untuk diajukan kembali. **Catat rujukan** mencatat bahwa warga sudah diarahkan ke layanan resmi.
4. Warga memantau perubahan status dalam WIB. Setelah kartu diterima dari layanan resmi, warga memilih **Konfirmasi kartu diterima**. Warga juga bisa membatalkan permohonan yang masih berjalan.

Buku I Tabel 26 dan 30 mengusulkan simpul layanan Pakiskembar. Tabel 16 mencatat SOP desa masih perlu observasi. Alur di atas adalah simulasi dengan data fiktif dan pemberitahuan di setiap halaman. Lokerin menyimpan data pendampingan dari akun yang sudah aktif tanpa mengunggah dokumen. Sebelum uji coba dengan warga, konfirmasi pembagian peran, verifikasi domisili, dan serah terima ke petugas resmi. [Disnaker Kabupaten Malang](https://disnaker.malangkab.go.id/) · [Kecamatan Pakis](https://pakis.malangkab.go.id/) · [Panduan Karirhub](https://panduan.kemnaker.go.id/panduan/20)

### Persiapan integrasi SIAPkerja

Buka **Persiapan integrasi SIAPkerja** dari dasbor Disnaker untuk meninjau judul lowongan, nama usaha, desa, jenis kerja, pendidikan minimum, tenggat, dan tautan publik Lokerin. Pratinjau memakai aturan katalog publik dan hanya menampilkan lowongan Pakis yang masih menerima lamaran. Seluruh data pada halaman ini dibaca dari Lokerin.

Buku I mengusulkan Lokerin terhubung ke SIAPkerja. Permenaker 17/2024 Pasal 8 mengatur interoperabilitas melalui kerja sama. Untuk melanjutkan Tiket 11, Disnaker perlu memperoleh kontrak resmi, cakupan dan arah pertukaran yang disetujui, serta akses ke lingkungan uji dari Kemnaker. Pemetaan bidang, autentikasi, dan pengujian pembaruan atau penutupan lowongan mengikuti kontrak tersebut. Profil warga, lamaran, dan penempatan berada di luar cakupan pratinjau. [Permenaker 17/2024, Pasal 8](https://jdih.kemnaker.go.id/asset/data_puu/2024pmnaker017.pdf) · [Skema integrasi Karirhub](https://paskerid.kemnaker.go.id/news/86)

### Rekap Command Center

1. Masuk sebagai Disnaker, buka **Rekap Command Center** dari dasbor, dan tinjau jumlah pencari kerja, lowongan aktif, lamaran, serta penempatan per desa.
2. Pilih **Setujui rekap** untuk menyimpan salinan angka yang ditinjau beserta waktu persetujuan. Jika angka atau tanggal berubah sejak halaman dibuka, tinjau ulang data terbaru. Persetujuan ulang terhadap angka dan tanggal yang sama menggunakan salinan yang sudah tersimpan.
3. Buka **Rekap yang disetujui** lalu pilih **Unduh JSON**. Berkas memuat agregat per desa, total Kecamatan Pakis, tanggal rekap dan waktu persetujuan dalam WIB. Identitas warga serta identitas akun penyetuju tersimpan di luar berkas unduhan.

Format JSON tersebut untuk pemeriksaan lokal. Penyampaian ke Command Center Kabupaten Malang masih menunggu spesifikasi penerimaan dan akses uji dari Diskominfo, termasuk definisi indikator dan kode wilayah yang akan dipakai. [Command Center Kabupaten Malang](https://malangkab.go.id/Content/detail/diskominfo-bersinergi-dengan-greenblue-korea-wujudkan-smart-city-kabupaten-malang) · [Forum Satu Data Kabupaten Malang](https://ppid.malangkab.go.id/seputar/berita/forum-satu-data-kabupaten-malang-perkuat-sinergi-pengelolaan-data-dan-sosialisasi-dashboard-epic)
4. Login sebagai usaha → ubah status Rizky ke wawancara lalu diterima *(menunggu fitur F-09)*.
5. Login kader/DisNaKer → angka penempatan bertambah.
