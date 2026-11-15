@extends('layouts.app', ['judul' => 'Rekomendasi'])
@section('konten')
<h1 class="text-xl font-bold">Halo, {{ auth()->user()->name }}</h1>
<p class="text-sm text-slate-500 mb-4">Lowongan yang cocok untuk {{ $profil->pendidikan_terakhir }} {{ $profil->jurusan }} di sekitar Desa {{ auth()->user()->desa?->nama }}.</p>
<div class="grid sm:grid-cols-2 gap-3">
    @forelse ($rekomendasi as $r)
        <div>
            @include('partials.kartu-lowongan', ['l' => $r['lowongan'], 'hasil' => $r['hasil']])
            @if (in_array($r['lowongan']->id, $sudahDilamar))<p class="text-xs text-green-700 mt-1">Sudah dilamar</p>@endif
        </div>
    @empty
        <p class="text-slate-500 text-sm">Belum ada lowongan dengan skor di atas {{ config('lokerin.ambang_rekomendasi') }}. Coba tambah keahlian di profil.</p>
    @endforelse
</div>
@endsection
