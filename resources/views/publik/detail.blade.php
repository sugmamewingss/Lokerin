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
    @if ($hasil)
        @include('partials.ringkasan-kecocokan', ['lowongan' => $lowongan, 'hasil' => $hasil])
    @endif
    <p class="mt-4 whitespace-pre-line">{{ $lowongan->deskripsi }}</p>
    <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
        <dt class="text-slate-500">Pendidikan minimal</dt><dd>{{ $lowongan->pendidikan_min }}</dd>
        <dt class="text-slate-500">Jurusan</dt><dd>{{ $lowongan->jurusan_target ?? 'Semua jurusan' }}</dd>
        <dt class="text-slate-500">Jenis kerja</dt><dd>{{ \App\Models\Lowongan::JENIS_KERJA[$lowongan->jenis_kerja] }}</dd>
        <dt class="text-slate-500">Kuota</dt><dd>{{ $lowongan->kuota }} orang</dd>
        @if ($gaji = $lowongan->rentangGaji())
            <dt class="text-slate-500">Gaji</dt><dd>{{ $gaji }}</dd>
        @endif
        @if ($lowongan->tanggal_tutup)
            <dt class="text-slate-500">Batas melamar</dt><dd>{{ $lowongan->tanggal_tutup->format('d/m/Y') }}</dd>
        @endif
    </dl>
    @include('partials.informasi-banjir', ['desaLokasi' => $lowongan->usaha->desa])
    <h2 class="mt-5 font-semibold">Keahlian yang dibutuhkan</h2>
    @if ($lowongan->keahlian->isEmpty())
        <p class="mt-2 text-sm text-slate-600">Belum ada keahlian yang dicantumkan.</p>
    @else
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
            @foreach ($lowongan->keahlian as $k)
                <li>
                    {{ $k->nama }}
                    @if (!$k->pivot->wajib)
                        <span class="text-slate-600">(tambahan)</span>
                    @elseif ($hasil && in_array($k->id, $hasil['keahlian_kurang'], true))
                        <span class="text-red-700">(wajib, belum dimiliki)</span>
                    @elseif ($hasil)
                        <span class="text-green-700">(wajib, terpenuhi)</span>
                    @else
                        <span class="text-slate-600">(wajib)</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
    @if ($pelatihan->isNotEmpty())
        <section id="pelatihan-terkait" class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">
            <h2 class="font-semibold">{{ $hasil ? 'Saran pelatihan untuk keahlian yang belum kamu miliki' : 'Pelatihan untuk keahlian wajib lowongan ini' }}</h2>
            <ul class="mt-1 list-disc pl-5">
                @foreach ($pelatihan as $p)
                    <li><a href="{{ route('pelatihan.show', $p) }}" class="font-medium text-blue-700 underline">{{ $p->judul }}</a> · {{ $p->keahlian->nama }}</li>
                @endforeach
            </ul>
        </section>
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
