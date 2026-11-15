@extends('layouts.app', ['judul' => 'Lamaran saya'])
@section('konten')
<h1 class="text-xl font-bold mb-4">Lamaran saya</h1>
<div class="bg-white rounded-xl border border-slate-200 divide-y">
    @forelse ($lamaran as $lm)
        <div class="p-4 flex items-center justify-between gap-3">
            <div>
                <p class="font-semibold">{{ $lm->lowongan->judul }}</p>
                <p class="text-sm text-slate-500">{{ $lm->lowongan->usaha->nama_usaha }} · {{ $lm->lowongan->usaha->desa->nama }} · skor {{ $lm->skor_cocok }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-slate-100 capitalize">{{ $lm->status }}</span>
        </div>
    @empty
        <p class="p-4 text-sm text-slate-500">Belum ada lamaran.</p>
    @endforelse
</div>
@endsection
