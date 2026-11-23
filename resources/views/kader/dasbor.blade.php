@extends('layouts.app', ['judul' => 'Dasbor desa'])
@section('konten')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="eyebrow">Kader desa</p>
        <h1 class="judul-halaman mt-2">Dasbor Desa {{ $desa->nama }}</h1>
        <p class="mt-1 text-sm text-slate-600">Kecamatan {{ $desa->kecamatan }} · pantau pencari kerja, lowongan, dan penempatan di desamu.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if ($desa->simpulAk1())
            <a href="{{ route('ak1.index') }}" class="btn btn-secondary">Pendampingan AK/1</a>
        @endif
        <a href="{{ route('kader.publikasi-lowongan') }}" class="btn btn-secondary">Bagikan lowongan</a>
        <a href="{{ route('kader.warga.index') }}" class="btn btn-primary">Daftarkan warga</a>
    </div>
</div>
@php $ikonAngka = ['warga', 'koper', 'dokumen', 'centang']; $warnaAngka = ['bg-brand-50 text-brand-700', 'bg-amber-50 text-amber-700', 'bg-sky-50 text-sky-700', 'bg-violet-50 text-violet-700']; @endphp
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ($angka as $label => $nilai)
        <div class="stat">
            <span class="grid h-10 w-10 place-items-center rounded-xl {{ $warnaAngka[$loop->index % 4] }}"><x-ikon :nama="$ikonAngka[$loop->index % 4]" /></span>
            <p class="stat-angka mt-4">{{ $nilai }}</p>
            <p class="stat-label">{{ $label }}</p>
        </div>
    @endforeach
</div>
@if ($menunggu)
    <a href="{{ route('kader.verifikasi') }}" class="mt-4 flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-900 transition hover:border-amber-300 hover:bg-amber-100">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-amber-400 text-amber-950"><x-ikon nama="toko" /></span>
        <span class="flex-1">{{ $menunggu }} usaha menunggu verifikasi →</span>
    </a>
@endif
@if ($ujiCoba)
    @include('partials.indikator-mangliawan', ['ujiCoba' => $ujiCoba])
@endif

<section class="card mt-6 p-4" aria-labelledby="pencari-per-jurusan">
    <h2 id="pencari-per-jurusan" class="mb-3 font-semibold">Pencari kerja per jurusan</h2>
    @php $terbanyak = $perJurusan->max('jumlah'); @endphp
    <ul class="space-y-3 text-sm">
        @forelse ($perJurusan as $j)
            <li>
                <div class="flex justify-between gap-2">
                    <span>{{ $j->jurusan ?? 'Belum diisi' }}</span>
                    <span class="font-medium tabular-nums">{{ $j->jumlah }}</span>
                </div>
                <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
                    <div class="h-full rounded-full bg-brand-600" style="width: {{ 100 * $j->jumlah / $terbanyak }}%"></div>
                </div>
            </li>
        @empty
            <li class="text-slate-600">Belum ada profil dengan persetujuan aktif.</li>
        @endforelse
    </ul>
</section>
@endsection
