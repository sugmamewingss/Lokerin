{{-- Pakai: @include('partials.kartu-lowongan', ['l' => $lowongan, 'hasil' => $hasil ?? null]) --}}
@php
    $namaUsaha = $l->usaha->nama_usaha;
    $palet = ['bg-brand-100 text-brand-800', 'bg-amber-100 text-amber-800', 'bg-sky-100 text-sky-800', 'bg-rose-100 text-rose-800', 'bg-violet-100 text-violet-800', 'bg-teal-100 text-teal-800'];
    $warnaInisial = $palet[crc32($namaUsaha) % count($palet)];
@endphp
<a href="{{ route('lowongan.detail', $l) }}" class="card group flex h-full min-w-0 flex-col p-5 transition duration-200 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-angkat">
    <div class="flex items-start gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-base font-extrabold {{ $warnaInisial }}">{{ mb_strtoupper(mb_substr($namaUsaha, 0, 1)) }}</span>
        <div class="min-w-0 flex-1">
            <h3 class="font-bold leading-snug text-slate-900 group-hover:text-brand-800">{{ $l->judul }}</h3>
            <p class="mt-0.5 truncate text-sm text-slate-500">{{ $l->usaha->nama_usaha }} · {{ $l->usaha->desa->nama }}</p>
        </div>
        @isset($hasil)
            <span @class([
                'shrink-0 rounded-full px-2.5 py-1 text-xs font-bold tabular-nums',
                'bg-brand-600 text-white' => $hasil['skor'] >= 75,
                'bg-brand-50 text-brand-800 ring-1 ring-brand-200' => $hasil['skor'] < 75,
            ])>{{ $hasil['skor'] }}% cocok</span>
        @endisset
    </div>
    <div class="mt-4 flex flex-wrap gap-1.5">
        <span class="chip"><x-ikon nama="jam" class="h-3.5 w-3.5" />{{ \App\Models\Lowongan::JENIS_KERJA[$l->jenis_kerja] }}</span>
        <span class="chip"><x-ikon nama="topi" class="h-3.5 w-3.5" />min. {{ $l->pendidikan_min }}</span>
        @if ($gaji = $l->rentangGaji())
            <span class="chip bg-brand-50 text-brand-800"><x-ikon nama="uang" class="h-3.5 w-3.5" />{{ $gaji }}</span>
        @endif
    </div>
    @isset($hasil)
        <div class="mt-3 border-t border-slate-100 pt-3">
            @include('partials.ringkasan-kecocokan', ['lowongan' => $l, 'hasil' => $hasil])
        </div>
    @endisset
    <span class="mt-auto flex items-center gap-1 pt-4 text-sm font-semibold text-brand-700 opacity-80 transition group-hover:gap-2 group-hover:opacity-100">Lihat detail <x-ikon nama="panah-kanan" class="h-4 w-4" /></span>
</a>
