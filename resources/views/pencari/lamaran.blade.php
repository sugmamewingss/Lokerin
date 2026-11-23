@extends('layouts.app', ['judul' => 'Lamaran saya'])
@section('konten')
<h1 class="judul-halaman mb-4">Lamaran saya</h1>
<div class="card divide-y">
    @forelse ($lamaran as $lm)
        <article class="p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold">{{ $lm->lowongan->judul }}</h2>
                    <p class="text-sm text-slate-600">{{ $lm->lowongan->usaha->nama_usaha }} · {{ $lm->lowongan->usaha->desa->nama }} · Skor {{ $lm->skor_cocok }}%</p>
                </div>
                <span class="rounded bg-slate-100 px-2 py-1 text-sm font-medium">{{ \App\Models\Lamaran::STATUS[$lm->status] }}</span>
            </div>
            @if ($lm->status === 'diterima' && $lm->penempatan)
                @include('partials.ringkasan-penempatan', ['penempatan' => $lm->penempatan])
            @endif
            @if ($lm->bolehPindahKe('dibatalkan'))
                <form method="POST" action="{{ route('pencari.lamaran.batal', $lm) }}" class="mt-3">
                    @csrf @method('PATCH')
                    <button class="btn btn-secondary px-3 py-2 font-medium text-red-700">Batalkan lamaran</button>
                </form>
            @endif
        </article>
    @empty
        <p class="p-4 text-sm text-slate-500">Belum ada lamaran.</p>
    @endforelse
</div>
@endsection
