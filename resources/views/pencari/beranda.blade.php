@extends('layouts.app', ['judul' => 'Rekomendasi'])
@section('konten')
<div class="mb-3 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold">Halo, {{ auth()->user()->name }}</h1>
        <p class="text-sm text-slate-600">
            Rekomendasi untuk profil {{ $profil->pendidikan_terakhir }} {{ $profil->jurusan }}
            @if ($namaDesa = $profil->user?->desa?->nama) dari {{ $namaDesa }} @endif
        </p>
    </div>
    <nav aria-label="Urutan rekomendasi" class="inline-flex flex-wrap gap-1 rounded-lg border border-slate-200 bg-white p-1 text-sm">
        <a href="{{ route('pencari.beranda') }}" @if (!$terdekat) aria-current="true" @endif
            @class(['rounded-md px-3 py-2', 'bg-blue-600 font-semibold text-white' => !$terdekat, 'text-slate-700 hover:bg-slate-100' => $terdekat])>Paling cocok</a>
        <a href="{{ route('pencari.beranda', ['urut' => 'jarak']) }}" @if ($terdekat) aria-current="true" @endif
            @class(['rounded-md px-3 py-2', 'bg-blue-600 font-semibold text-white' => $terdekat, 'text-slate-700 hover:bg-slate-100' => !$terdekat])>Terdekat</a>
    </nav>
</div>
<p class="mb-4 text-sm text-slate-600">
    Jarak dihitung dalam garis lurus antartitik pusat desa atau kelurahan.
    @if ($terdekat)
        Lowongan dengan jarak yang belum tersedia tampil setelah lokasi yang bisa dihitung.
    @else
        Jika skor sama, lowongan yang lebih dekat tampil lebih dulu.
    @endif
</p>
<div class="grid sm:grid-cols-2 gap-3">
    @forelse ($rekomendasi as $r)
        <div>
            @include('partials.kartu-lowongan', ['l' => $r['lowongan'], 'hasil' => $r['hasil']])
            @if ($r['pelatihan']->isNotEmpty())
                <a href="{{ route('lowongan.detail', $r['lowongan']) }}#pelatihan-terkait" class="mt-2 inline-block text-sm font-medium text-blue-700 underline">Lihat pelatihan terkait<span class="sr-only"> untuk {{ $r['lowongan']->judul }}</span></a>
            @endif
            @if (in_array($r['lowongan']->id, $sudahDilamar))<p class="text-xs text-green-700 mt-1">Sudah dilamar</p>@endif
        </div>
    @empty
        <p class="text-slate-600 text-sm">Belum ada lowongan yang mencapai skor {{ config('lokerin.ambang_rekomendasi') }}. Coba tambah keahlian di profil.</p>
    @endforelse
</div>
@endsection
