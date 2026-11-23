@extends('layouts.app', ['judul' => 'Lowongan'])
@section('konten')
<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="eyebrow">Lowongan kerja</p>
        <h1 class="judul-halaman mt-2">Lowongan di Kecamatan Pakis</h1>
        <p class="mt-2 text-sm text-slate-600">{{ $lowongan->total() }} lowongan dari usaha terverifikasi di desa-desa sekitarmu.</p>
    </div>
</div>
<form class="card mt-6 flex flex-col gap-3 p-3 sm:flex-row sm:items-center">
    <div class="flex flex-1 items-center gap-2 rounded-xl bg-slate-50 px-3">
        <x-ikon nama="lokasi" class="h-5 w-5 shrink-0 text-slate-400" />
        <select name="desa" aria-label="Filter desa" class="w-full border-0 bg-transparent shadow-none focus:ring-0">
            <option value="">Semua desa</option>
            @foreach ($desa as $d)<option value="{{ $d->id }}" @selected(request('desa') == $d->id)>{{ $d->nama }}</option>@endforeach
        </select>
    </div>
    <div class="flex flex-1 items-center gap-2 rounded-xl bg-slate-50 px-3">
        <x-ikon nama="jam" class="h-5 w-5 shrink-0 text-slate-400" />
        <select name="jenis" aria-label="Filter jenis kerja" class="w-full border-0 bg-transparent shadow-none focus:ring-0">
            <option value="">Semua jenis</option>
            @foreach (\App\Models\Lowongan::JENIS_KERJA as $v => $t)
                <option value="{{ $v }}" @selected(request('jenis') == $v)>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary px-6"><x-ikon nama="cari" class="h-4 w-4" />Terapkan</button>
    @if (request('desa') || request('jenis'))
        <a href="{{ route('lowongan.index') }}" class="btn btn-ghost">Reset</a>
    @endif
</form>
<div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($lowongan as $l)
        @include('partials.kartu-lowongan', ['l' => $l])
    @empty
        <div class="card flex flex-col items-center p-10 text-center sm:col-span-2 lg:col-span-3">
            <span class="grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400"><x-ikon nama="cari" class="h-6 w-6" /></span>
            <p class="mt-3 text-sm text-slate-500">Tidak ada lowongan yang cocok dengan filter.</p>
        </div>
    @endforelse
</div>
<div class="mt-6">{{ $lowongan->links() }}</div>
@endsection
