@extends('layouts.app', ['judul' => 'Rekomendasi'])
@section('konten')
<section class="relative overflow-hidden rounded-[2rem] bg-brand-950 p-6 text-white shadow-angkat sm:p-8">
    <div class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image: radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0); background-size: 22px 22px;"></div>
    <div class="pointer-events-none absolute -right-16 -top-20 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>
    <div class="relative flex flex-wrap items-end justify-between gap-6">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-brand-300">Rekomendasi untukmu</p>
            <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-white">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-sm text-brand-100/90">
                Rekomendasi untuk profil {{ $profil->pendidikan_terakhir }} {{ $profil->jurusan }}
                @if ($namaDesa = $profil->user?->desa?->nama) dari {{ $namaDesa }} @endif
            </p>
        </div>
        <nav aria-label="Urutan rekomendasi" class="inline-flex flex-wrap gap-1 rounded-2xl bg-white/10 p-1 text-sm ring-1 ring-white/15">
            <a href="{{ route('pencari.beranda') }}" @if (!$terdekat) aria-current="true" @endif
                @class(['rounded-xl px-4 py-2 font-semibold transition', 'bg-white text-brand-900 shadow' => !$terdekat, 'text-brand-100 hover:bg-white/10' => $terdekat])>Paling cocok</a>
            <a href="{{ route('pencari.beranda', ['urut' => 'jarak']) }}" @if ($terdekat) aria-current="true" @endif
                @class(['rounded-xl px-4 py-2 font-semibold transition', 'bg-white text-brand-900 shadow' => $terdekat, 'text-brand-100 hover:bg-white/10' => !$terdekat])>Terdekat</a>
        </nav>
    </div>
</section>
<p class="mb-5 mt-5 flex items-start gap-2 text-sm text-slate-600">
    <x-ikon nama="info" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
    <span>
    Jarak dihitung dalam garis lurus antartitik pusat desa atau kelurahan.
    @if ($terdekat)
        Lowongan dengan jarak yang belum tersedia tampil setelah lokasi yang bisa dihitung.
    @else
        Jika skor sama, lowongan yang lebih dekat tampil lebih dulu.
    @endif
    </span>
</p>
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($rekomendasi as $r)
        <div class="flex min-w-0 flex-col">
            @include('partials.kartu-lowongan', ['l' => $r['lowongan'], 'hasil' => $r['hasil']])
            @if ($r['pelatihan']->isNotEmpty())
                <a href="{{ route('lowongan.detail', $r['lowongan']) }}#pelatihan-terkait" class="link mt-2 inline-flex items-center gap-1 px-1 text-sm">Lihat pelatihan terkait<span class="sr-only"> untuk {{ $r['lowongan']->judul }}</span></a>
            @endif
            @if (in_array($r['lowongan']->id, $sudahDilamar))<p class="mt-2 inline-flex items-center gap-1 px-1 text-xs font-semibold text-brand-700"><x-ikon nama="centang" class="h-4 w-4" />Sudah dilamar</p>@endif
        </div>
    @empty
        <p class="text-slate-600 text-sm">Belum ada lowongan yang mencapai skor {{ config('lokerin.ambang_rekomendasi') }}. Coba tambah keahlian di profil.</p>
    @endforelse
</div>
@endsection
