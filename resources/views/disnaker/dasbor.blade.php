@extends('layouts.app', ['judul' => 'Dasbor kecamatan'])
@section('konten')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="eyebrow">Disnaker Kabupaten Malang</p>
        <h1 class="judul-halaman mt-2">Dasbor Kecamatan Pakis</h1>
        <p class="mt-1 text-sm text-slate-600">Rekap ketenagakerjaan 15 desa dan indikator uji coba Lokerin.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('disnaker.siapkerja.index') }}" class="btn btn-secondary">Persiapan integrasi SIAPkerja</a>
        <a href="{{ route('disnaker.command-center.index') }}" class="btn btn-secondary">Rekap Command Center</a>
    </div>
</div>
@if ($ujiCoba)
    @include('partials.indikator-mangliawan', ['ujiCoba' => $ujiCoba])
@endif
<h2 class="mb-1 mt-10 text-xl font-extrabold">Rekap per desa</h2>
<p class="mb-3 text-sm text-slate-600">Lowongan aktif mengikuti kuota tersisa. Lamaran dan penempatan dikelompokkan menurut desa pencari kerja dengan persetujuan aktif.</p>
@include('disnaker.partials.tabel-rekap-desa')
@endsection
