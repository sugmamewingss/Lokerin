@extends('layouts.app', ['judul' => 'Beranda'])
@section('konten')
<section class="rounded-2xl bg-blue-600 text-white p-6 sm:p-10">
    <h1 class="text-2xl sm:text-3xl font-bold">Kerja dekat rumah, sesuai jurusanmu.</h1>
    <p class="mt-2 text-blue-100 max-w-xl">Lokerin mempertemukan warga Kecamatan Pakis dengan lowongan dari UMKM dan usaha di sekitarnya.</p>
    <div class="mt-5 flex gap-3">
        <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-white text-blue-700 font-semibold">Daftar gratis</a>
        <a href="{{ route('lowongan.index') }}" class="px-4 py-2 rounded-lg border border-blue-300">Lihat {{ $jumlahLowongan }} lowongan</a>
    </div>
</section>
<h2 class="mt-8 mb-3 font-semibold">Lowongan terbaru</h2>
<div class="grid sm:grid-cols-2 gap-3">
    @forelse ($terbaru as $l)
        @include('partials.kartu-lowongan', ['l' => $l])
    @empty
        <p class="text-slate-500 text-sm">Belum ada lowongan.</p>
    @endforelse
</div>
@endsection
