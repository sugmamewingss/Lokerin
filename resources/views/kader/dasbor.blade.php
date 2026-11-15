@extends('layouts.app', ['judul' => 'Dasbor desa'])
@section('konten')
<h1 class="text-xl font-bold mb-4">Dasbor Desa {{ $desa->nama }}</h1>
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    @foreach ($angka as $label => $nilai)
        <div class="bg-white rounded-xl border p-4"><p class="text-2xl font-bold">{{ $nilai }}</p><p class="text-xs text-slate-500">{{ $label }}</p></div>
    @endforeach
</div>
@if ($menunggu)
    <a href="{{ route('kader.verifikasi') }}" class="block mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-sm">{{ $menunggu }} usaha menunggu verifikasi →</a>
@endif
<h2 class="mt-6 mb-2 font-semibold">Pencari kerja per jurusan</h2>
<table class="w-full bg-white rounded-xl border text-sm">
    @foreach ($perJurusan as $j)<tr class="border-b"><td class="p-2">{{ $j->jurusan ?? 'Belum diisi' }}</td><td class="p-2 text-right">{{ $j->jumlah }}</td></tr>@endforeach
</table>
<p class="mt-2 text-xs text-slate-400">TODO (Muflih): ganti tabel ini dengan grafik Chart.js.</p>
@endsection
