@extends('layouts.app', ['judul' => 'Dasbor desa'])
@section('konten')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-xl font-bold">Dasbor Desa {{ $desa->nama }}</h1>
    <div class="flex flex-wrap gap-2">
        @if ($desa->simpulAk1())
            <a href="{{ route('ak1.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700">Pendampingan AK/1</a>
        @endif
        <a href="{{ route('kader.publikasi-lowongan') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-blue-700">Bagikan lowongan</a>
        <a href="{{ route('kader.warga.index') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Daftarkan warga</a>
    </div>
</div>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    @foreach ($angka as $label => $nilai)
        <div class="bg-white rounded-xl border p-4"><p class="text-2xl font-bold">{{ $nilai }}</p><p class="text-xs text-slate-500">{{ $label }}</p></div>
    @endforeach
</div>
@if ($menunggu)
    <a href="{{ route('kader.verifikasi') }}" class="block mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">{{ $menunggu }} usaha menunggu verifikasi →</a>
@endif
@if ($ujiCoba)
    @include('partials.indikator-mangliawan', ['ujiCoba' => $ujiCoba])
@endif

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-4" aria-labelledby="pencari-per-jurusan">
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
                    <div class="h-full rounded-full bg-blue-600" style="width: {{ 100 * $j->jumlah / $terbanyak }}%"></div>
                </div>
            </li>
        @empty
            <li class="text-slate-600">Belum ada profil dengan persetujuan aktif.</li>
        @endforelse
    </ul>
</section>
@endsection
