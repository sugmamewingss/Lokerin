@extends('layouts.app', ['judul' => 'Katalog pelatihan'])

@section('konten')
<h1 class="mb-4 text-xl font-bold">Katalog pelatihan</h1>
<form method="GET" action="{{ route('pelatihan.index') }}" class="mb-4 flex flex-wrap items-end gap-2 text-sm">
    <label class="block">Keahlian
        <select name="keahlian" class="mt-1 block rounded-lg border-slate-300">
            <option value="">Semua keahlian</option>
            @foreach ($keahlian as $item)
                <option value="{{ $item->id }}" @selected($keahlianId === $item->id)>{{ $item->nama }}</option>
            @endforeach
        </select>
    </label>
    <button class="rounded-lg bg-blue-600 px-3 py-2 font-semibold text-white">Terapkan</button>
</form>

<div class="grid gap-3 sm:grid-cols-2">
    @forelse ($pelatihan as $item)
        <a href="{{ route('pelatihan.show', $item) }}" class="block rounded-xl border border-slate-200 bg-white p-4 hover:border-blue-400">
            <h2 class="font-semibold text-blue-700">{{ $item->judul }}</h2>
            <p class="mt-1 text-sm text-slate-600">{{ $item->penyelenggara }} · {{ $item->keahlian->nama }}</p>
            @if ($item->jadwal)
                <p class="mt-1 text-sm text-slate-600">{{ $item->jadwal }}</p>
            @endif
        </a>
    @empty
        <p class="text-sm text-slate-600">Belum ada pelatihan untuk pilihan ini.</p>
    @endforelse
</div>
<div class="mt-4">{{ $pelatihan->links() }}</div>
@endsection
