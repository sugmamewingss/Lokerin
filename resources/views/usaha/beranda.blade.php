@extends('layouts.app', ['judul' => 'Usaha saya'])
@section('konten')
@if (!$usaha)
    <div class="bg-white rounded-xl border p-5">
        <h1 class="text-xl font-bold">Profil usaha belum ada</h1>
        <p class="text-sm text-slate-500 mt-1">TODO (Adefriko): form profil usaha.</p>
    </div>
@else
    <h1 class="text-xl font-bold">{{ $usaha->nama_usaha }}</h1>
    <p class="text-sm text-slate-500">Desa {{ $usaha->desa->nama }} · status verifikasi: <b>{{ $usaha->status_verifikasi }}</b></p>
    <h2 class="mt-5 mb-2 font-semibold">Lowongan</h2>
    <div class="bg-white rounded-xl border divide-y">
        @forelse ($usaha->lowongan as $l)
            <div class="p-4 flex justify-between"><span>{{ $l->judul }} <span class="text-xs text-slate-500">({{ $l->status }})</span></span><span class="text-sm">{{ $l->lamaran_count }} pelamar</span></div>
        @empty
            <p class="p-4 text-sm text-slate-500">Belum ada lowongan.</p>
        @endforelse
    </div>
    <p class="mt-3 text-xs text-slate-400">TODO (Adefriko): tombol pasang lowongan, halaman pelamar urut skor, ubah status.</p>
@endif
@endsection
