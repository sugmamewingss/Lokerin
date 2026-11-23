@extends('layouts.app', ['judul' => 'Kebijakan data'])

@section('konten')
<article class="card max-w-3xl space-y-5 p-5 sm:p-7">
    <header class="space-y-2">
        <h1 class="judul-halaman">Kebijakan data Lokerin</h1>
        <p class="text-sm text-slate-600">Lokerin menggunakan data akun untuk masuk ke aplikasi. Setelah kamu setuju, data profil pencari kerja dipakai untuk rekomendasi, proses lamaran, dan statistik tingkat desa.</p>
    </header>

    <section aria-labelledby="akses-data">
        <h2 id="akses-data" class="mb-2 font-semibold">Siapa yang bisa melihat data</h2>
        <dl class="divide-y divide-slate-200 text-sm">
            <div class="py-3">
                <dt class="font-semibold">Publik</dt>
                <dd class="mt-1 text-slate-600">Siapa pun bisa melihat lowongan aktif dari usaha terverifikasi, nama usaha, dan desa lokasi kerja.</dd>
            </div>
            <div class="py-3">
                <dt class="font-semibold">Internal</dt>
                <dd class="mt-1 text-slate-600">Kader melihat rekap desanya dan data usaha yang dibutuhkan untuk verifikasi di desa tersebut. Saat mendampingi pendaftaran, kader mengisi nama dan nomor HP warga. Daftar aktivasi kader menampilkan nama dan empat angka terakhir nomor HP warga di desanya. Disnaker melihat rekap per desa. Usaha melihat jumlah pelamar di lowongannya sendiri.</dd>
            </div>
            <div class="py-3">
                <dt class="font-semibold">Terbatas</dt>
                <dd class="mt-1 text-slate-600">Pencari kerja bisa melihat profil dan riwayat lamarannya sendiri. Usaha terverifikasi hanya bisa melihat nama, pendidikan, jurusan, skor, dan status pelamar pada lowongannya selama persetujuan aktif. Nomor HP tersedia bagi usaha tersebut saat status lamaran Wawancara atau Diterima. Alamat, jenis usaha, dan nama pemilik tersedia bagi kader di desa lokasi usaha untuk verifikasi. Kata sandi disimpan dalam bentuk hash.</dd>
            </div>
        </dl>
    </section>

    <section aria-labelledby="persetujuan-data" class="space-y-2 border-t border-slate-200 pt-4">
        <h2 id="persetujuan-data" class="font-semibold">Mengelola persetujuanmu</h2>
        <p class="text-sm text-slate-600">Di halaman Profil, kamu bisa mengubah data dan menarik persetujuan. Setelah ditarik, rekomendasi dan pengiriman lamaran baru berhenti. Lamaranmu disembunyikan dari usaha dan dikeluarkan dari rekap, sementara riwayatnya tetap tersedia untukmu.</p>
        @auth
            @if (auth()->user()->hasRole(\App\Models\User::ROLE_PENCARI))
                <a href="{{ route('pencari.profil') }}" class="inline-block text-sm link">Buka profil pencari kerja</a>
            @endif
        @endauth
    </section>
    <section aria-labelledby="data-ak1" class="space-y-2 border-t border-slate-200 pt-4">
        <h2 id="data-ak1" class="font-semibold">Pendampingan AK/1</h2>
        <p class="text-sm text-slate-600">Pencatatan permohonan AK/1 memiliki persetujuan tersendiri. Pemohon dan kader Pakiskembar yang menangani pendampingan dapat melihat nama pemohon, status, dan riwayat perubahan. Kader memakai nomor HP yang sudah terdaftar untuk menemukan akun warga sesuai persetujuan warga. Berkas identitas disampaikan melalui layanan resmi. Pemohon dapat membatalkan permohonan yang masih berjalan.</p>
    </section>
</article>
@endsection
