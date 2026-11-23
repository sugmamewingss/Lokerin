@extends('layouts.app', ['judul' => 'Dasbor kecamatan'])
@section('konten')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-xl font-bold">Dasbor Kecamatan Pakis</h1>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('disnaker.siapkerja.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700">Persiapan integrasi SIAPkerja</a>
        <a href="{{ route('disnaker.command-center.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700">Rekap Command Center</a>
    </div>
</div>
@if ($ujiCoba)
    @include('partials.indikator-mangliawan', ['ujiCoba' => $ujiCoba])
@endif
<h2 class="mb-3 text-lg font-semibold">Rekap per desa</h2>
<p class="mb-3 text-sm text-slate-600">Lowongan aktif mengikuti kuota tersisa. Lamaran dan penempatan dikelompokkan menurut desa pencari kerja dengan persetujuan aktif.</p>
@include('disnaker.partials.tabel-rekap-desa')
@endsection
