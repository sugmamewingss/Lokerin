@php
    $jurusan = match (true) {
        blank($lowongan->jurusan_target) => 'Terbuka untuk semua jurusan',
        $hasil['J'] === 1.0 => 'Jurusan sesuai',
        $hasil['J'] > 0 => 'Jurusan serumpun',
        default => 'Jurusan belum cocok',
    };
    $jumlahWajib = $hasil['jumlah_keahlian_wajib'];
    $jumlahTerpenuhi = $jumlahWajib - count($hasil['keahlian_kurang']);
    $keahlian = $jumlahWajib ? "$jumlahTerpenuhi dari $jumlahWajib keahlian wajib terpenuhi" : 'Tanpa keahlian wajib';
    $lokasi = match ($hasil['kategori_lokasi']) {
        'desa_sama' => 'Satu desa',
        'kecamatan_sama' => 'Satu kecamatan',
        'luar' => 'Beda kecamatan',
        default => 'Lokasi asal atau tujuan belum diketahui',
    };
@endphp
<p class="mt-2 text-sm text-slate-600">
    {{ $jurusan }} · {{ $keahlian }} · {{ $lokasi }}
    @if ($hasil['kategori_lokasi'] !== 'desa_sama')
        · {{ $hasil['jarak_km'] === null ? 'Jarak belum tersedia' : 'Sekitar '.number_format($hasil['jarak_km'], 1, ',', '.').' km antarpusat desa' }}
    @endif
</p>
