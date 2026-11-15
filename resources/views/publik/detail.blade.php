@extends('layouts.app', ['judul' => $lowongan->judul])
@section('konten')
<article class="bg-white rounded-xl border border-slate-200 p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold">{{ $lowongan->judul }}</h1>
            <p class="text-slate-500">{{ $lowongan->usaha->nama_usaha }} · Desa {{ $lowongan->usaha->desa->nama }}</p>
        </div>
        @if ($hasil)<span class="px-3 py-1 rounded-lg bg-blue-600 text-white font-semibold">{{ $hasil['skor'] }}% cocok</span>@endif
    </div>
    <p class="mt-4 whitespace-pre-line">{{ $lowongan->deskripsi }}</p>
    <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
        <dt class="text-slate-500">Pendidikan minimal</dt><dd>{{ $lowongan->pendidikan_min }}</dd>
        <dt class="text-slate-500">Jurusan</dt><dd>{{ $lowongan->jurusan_target ?? 'Semua jurusan' }}</dd>
        <dt class="text-slate-500">Jenis kerja</dt><dd>{{ str_replace('_', ' ', $lowongan->jenis_kerja) }}</dd>
        <dt class="text-slate-500">Kuota</dt><dd>{{ $lowongan->kuota }} orang</dd>
    </dl>
    <h2 class="mt-5 font-semibold">Keahlian yang dibutuhkan</h2>
    <ul class="mt-2 space-y-1 text-sm">
        @foreach ($lowongan->keahlian as $k)
            @php $kurang = $hasil && in_array($k->id, $hasil['keahlian_kurang']); @endphp
            <li>{!! $hasil ? ($kurang ? '<span class="text-red-600">✗</span>' : '<span class="text-green-600">✓</span>') : '•' !!}
                {{ $k->nama }} @unless($k->pivot->wajib)<span class="text-slate-400">(nilai tambah)</span>@endunless</li>
        @endforeach
    </ul>
    @if ($pelatihan->isNotEmpty())
        <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
            <p class="font-semibold">Saran pelatihan untuk keahlian yang belum kamu miliki</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($pelatihan as $p)<li>{{ $p->judul }} ({{ $p->penyelenggara }}, {{ $p->jadwal }})</li>@endforeach
            </ul>
        </div>
    @endif
    <div class="mt-5">
        @if ($profil && $profil->isAktif())
            <form method="POST" action="{{ route('pencari.lamar', $lowongan) }}">@csrf
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">Lamar sekarang</button>
            </form>
        @elseif (!auth()->check())
            <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Masuk untuk melamar</a>
        @endif
    </div>
</article>
@endsection
