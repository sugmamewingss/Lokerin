@extends('layouts.app', ['judul' => $lowongan->judul])
@section('konten')
@php $namaUsaha = $lowongan->usaha->nama_usaha; @endphp
<a href="{{ route('lowongan.index') }}" class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-brand-700"><x-ikon nama="panah-kanan" class="h-4 w-4 rotate-180" />Semua lowongan</a>
<div class="grid items-start gap-6 lg:grid-cols-[1fr_20rem]">
    <article class="card p-6 sm:p-8">
        <div class="flex items-start gap-4">
            <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-brand-100 text-xl font-extrabold text-brand-800">{{ mb_strtoupper(mb_substr($namaUsaha, 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl">{{ $lowongan->judul }}</h1>
                <p class="mt-1 flex flex-wrap items-center gap-1 text-slate-500"><x-ikon nama="toko" class="h-4 w-4" />{{ $lowongan->usaha->nama_usaha }} · Desa {{ $lowongan->usaha->desa->nama }}</p>
            </div>
            @if ($hasil)<span class="shrink-0 rounded-full bg-brand-600 px-3 py-1.5 text-sm font-bold text-white">{{ $hasil['skor'] }}% cocok</span>@endif
        </div>
        @if ($hasil)
            <div class="mt-4 rounded-2xl bg-brand-50 px-4 py-3 ring-1 ring-brand-100">
                @include('partials.ringkasan-kecocokan', ['lowongan' => $lowongan, 'hasil' => $hasil])
            </div>
        @endif

        <h2 class="mt-8 text-lg font-bold">Deskripsi pekerjaan</h2>
        <p class="mt-2 whitespace-pre-line leading-relaxed text-slate-700">{{ $lowongan->deskripsi }}</p>

        <h2 class="mt-8 text-lg font-bold">Keahlian yang dibutuhkan</h2>
        @if ($lowongan->keahlian->isEmpty())
            <p class="mt-2 text-sm text-slate-600">Belum ada keahlian yang dicantumkan.</p>
        @else
            <ul class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                @foreach ($lowongan->keahlian as $k)
                    @php
                        $kurang = $k->pivot->wajib && $hasil && in_array($k->id, $hasil['keahlian_kurang'], true);
                        $terpenuhi = $k->pivot->wajib && $hasil && !$kurang;
                    @endphp
                    <li @class([
                        'flex items-start gap-2 rounded-xl border px-3 py-2.5',
                        'border-red-200 bg-red-50' => $kurang,
                        'border-brand-200 bg-brand-50' => $terpenuhi,
                        'border-slate-200 bg-white' => !$kurang && !$terpenuhi,
                    ])>
                        <x-ikon :nama="$kurang ? 'peringatan' : 'centang'" @class(['mt-0.5 h-4 w-4 shrink-0', 'text-red-600' => $kurang, 'text-brand-600' => $terpenuhi, 'text-slate-400' => !$kurang && !$terpenuhi]) />
                        <span>
                            <span class="font-medium text-slate-800">{{ $k->nama }}</span>
                            @if (!$k->pivot->wajib)
                                <span class="text-slate-600">(tambahan)</span>
                            @elseif ($kurang)
                                <span class="text-red-700">(wajib, belum dimiliki)</span>
                            @elseif ($hasil)
                                <span class="text-green-700">(wajib, terpenuhi)</span>
                            @else
                                <span class="text-slate-600">(wajib)</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($pelatihan->isNotEmpty())
            <section id="pelatihan-terkait" class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm">
                <h2 class="flex items-center gap-2 font-bold text-amber-950"><x-ikon nama="buku" class="h-5 w-5 text-amber-600" />{{ $hasil ? 'Saran pelatihan untuk keahlian yang belum kamu miliki' : 'Pelatihan untuk keahlian wajib lowongan ini' }}</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($pelatihan as $p)
                        <li class="flex flex-wrap items-center gap-x-2"><a href="{{ route('pelatihan.show', $p) }}" class="link">{{ $p->judul }}</a><span class="text-amber-900/70">· {{ $p->keahlian->nama }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        @include('partials.informasi-banjir', ['desaLokasi' => $lowongan->usaha->desa])
    </article>

    <aside class="space-y-4 lg:sticky lg:top-24">
        <div class="card p-6">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-500">Ringkasan</h2>
            <dl class="mt-4 space-y-3 text-sm">
                @foreach (array_filter([
                    ['topi', 'Pendidikan minimal', $lowongan->pendidikan_min],
                    ['buku', 'Jurusan', $lowongan->jurusan_target ?? 'Semua jurusan'],
                    ['jam', 'Jenis kerja', \App\Models\Lowongan::JENIS_KERJA[$lowongan->jenis_kerja]],
                    ['warga', 'Kuota', $lowongan->kuota.' orang'],
                    ($gaji = $lowongan->rentangGaji()) ? ['uang', 'Gaji', $gaji] : null,
                    $lowongan->tanggal_tutup ? ['kalender', 'Batas melamar', $lowongan->tanggal_tutup->format('d/m/Y')] : null,
                ]) as [$ikon, $label, $nilai])
                    <div class="flex items-start gap-3">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-500"><x-ikon :nama="$ikon" class="h-4 w-4" /></span>
                        <div><dt class="text-xs text-slate-500">{{ $label }}</dt><dd class="font-semibold text-slate-800">{{ $nilai }}</dd></div>
                    </div>
                @endforeach
            </dl>
            <div class="mt-6">
                @if ($profil && $profil->isAktif())
                    <form method="POST" action="{{ route('pencari.lamar', $lowongan) }}">@csrf
                        <button class="btn btn-primary w-full py-3">Lamar sekarang</button>
                    </form>
                @elseif (!auth()->check())
                    <a href="{{ route('login') }}" class="btn btn-primary w-full py-3">Masuk untuk melamar</a>
                @endif
            </div>
        </div>
    </aside>
</div>
@endsection
