@extends('layouts.app', ['judul' => 'Katalog pelatihan'])

@section('konten')
<p class="eyebrow">Tingkatkan keahlian</p>
<h1 class="judul-halaman mt-2">Katalog pelatihan</h1>
<p class="mt-2 text-sm text-slate-600">Pelatihan dari BLK, LPK, SMK, dan mitra lain untuk keahlian yang dicari usaha di Pakis.</p>
<form method="GET" action="{{ route('pelatihan.index') }}" class="card mt-6 flex flex-wrap items-end gap-3 p-4 text-sm">
    <label class="block flex-1 font-medium">Keahlian
        <select name="keahlian" class="mt-1.5 block w-full">
            <option value="">Semua keahlian</option>
            @foreach ($keahlian as $item)
                <option value="{{ $item->id }}" @selected($keahlianId === $item->id)>{{ $item->nama }}</option>
            @endforeach
        </select>
    </label>
    <button class="btn btn-primary px-6">Terapkan</button>
</form>

<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($pelatihan as $item)
        <a href="{{ route('pelatihan.show', $item) }}" class="card group flex min-w-0 flex-col p-5 transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-angkat">
            <span class="grid h-11 w-11 place-items-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100"><x-ikon nama="buku" class="h-6 w-6" /></span>
            <h2 class="mt-4 font-bold text-slate-900 group-hover:text-brand-800">{{ $item->judul }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ $item->penyelenggara }} · {{ $item->keahlian->nama }}</p>
            @if ($item->jadwal)
                <p class="mt-3 inline-flex items-center gap-1.5 text-sm text-slate-500"><x-ikon nama="kalender" class="h-4 w-4" />{{ $item->jadwal }}</p>
            @endif
        </a>
    @empty
        <p class="card p-6 text-sm text-slate-600 sm:col-span-2 lg:col-span-3">Belum ada pelatihan untuk pilihan ini.</p>
    @endforelse
</div>
<div class="mt-6">{{ $pelatihan->links() }}</div>
@endsection
