# Paket Windows: Lokerin.exe

Membungkus Lokerin menjadi satu file `Lokerin.exe` yang bisa dijalankan di Windows 64-bit
tanpa memasang XAMPP, Composer, atau Node. Saat diklik dua kali, `Lokerin.exe` membongkar
isinya ke `%LOCALAPPDATA%\Lokerin`, menyalakan server PHP di `127.0.0.1:8765`, lalu membuka browser.

## Membuat paket

Butuh PHP (XAMPP), Composer, dan Node di laptop pembuat paket.

1. Pastikan `composer install` dan `npm install` sudah pernah dijalankan di project.
2. Klik dua kali `tools\paket-exe\buat-exe.bat`.
3. Hasilnya ada di `tools\paket-exe\dist\Lokerin.exe` (folder `dist` tidak ikut di-commit).

Database demo dibuat ulang setiap kali paket dirakit, jadi isinya selalu data fiktif yang bersih.

## Catatan untuk pemakai

- Windows menampilkan peringatan SmartScreen karena file tidak bertanda tangan digital.
  Pilih **More info**, lalu **Run anyway**.
- Jalankan pertama kali butuh waktu lebih lama karena paket dibongkar dulu.
- Tutup jendela hitam untuk menghentikan Lokerin.
- Jika muncul error `VCRUNTIME140.dll` tidak ditemukan, pasang
  Microsoft Visual C++ Redistributable 2015-2022 (x64).
- Untuk mengulang data demo dari awal, hapus folder `%LOCALAPPDATA%\Lokerin`.

## Mengubah peluncur

Kode peluncur ada di `launcher/main.go`. Build ulang dengan Go:

```
cd tools/paket-exe/launcher
GOOS=windows GOARCH=amd64 go build -ldflags "-s -w" -o ../Lokerin-launcher.exe .
```

`rsrc_windows_amd64.syso` berisi ikon aplikasi dan ikut terbaca otomatis saat build.
