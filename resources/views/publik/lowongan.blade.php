@extends('layouts.app', ['judul' => 'Lowongan'])
@section('konten')
<h1 class="text-xl font-bold mb-4">Lowongan di Kecamatan Pakis</h1>
<form class="flex flex-wrap gap-2 mb-4 text-sm">
    <select name="desa" class="rounded-lg border-slate-300">
        <option value="">Semua desa</option>
        @foreach ($desa as $d)<option value="{{ $d->id }}" @selected(request('desa') == $d->id)>{{ $d->nama }}</option>@endforeach
    </select>
    <select name="jenis" class="rounded-lg border-slate-300">
        <option value="">Semua jenis</option>
        @foreach (['penuh_waktu' => 'Penuh waktu', 'paruh_waktu' => 'Paruh waktu', 'harian' => 'Harian'] as $v => $t)
            <option value="{{ $v }}" @selected(request('jenis') == $v)>{{ $t }}</option>
        @endforeach
    </select>
    <button class="px-3 py-2 rounded-lg bg-blue-600 text-white">Terapkan</button>
</form>
<div class="grid sm:grid-cols-2 gap-3">
    @forelse ($lowongan as $l)
        @include('partials.kartu-lowongan', ['l' => $l])
    @empty
        <p class="text-slate-500 text-sm">Tidak ada lowongan yang cocok dengan filter.</p>
    @endforelse
</div>
<div class="mt-4">{{ $lowongan->links() }}</div>
@endsection
