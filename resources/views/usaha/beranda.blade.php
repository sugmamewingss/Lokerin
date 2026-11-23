@extends('layouts.app', ['judul' => 'Usaha saya'])
@section('konten')
<div class="flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold">{{ $usaha?->nama_usaha ?? 'Usaha saya' }}</h1>
        @if ($usaha)
            <p class="text-sm text-slate-600">{{ $usaha->desa->nama }} · {{ ucfirst($usaha->status_verifikasi) }}@if ($usaha->status_verifikasi === 'menunggu') verifikasi @endif</p>
        @else
            <p class="text-sm text-slate-600">Lengkapi profil agar kader desa bisa memverifikasi usahamu.</p>
        @endif
    </div>
    <a href="{{ route('usaha.profil') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold">{{ $usaha ? 'Ubah profil' : 'Lengkapi profil' }}</a>
</div>

@if ($usaha)
    @if ($usaha->status_verifikasi !== 'terverifikasi')
        <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
            @if ($usaha->status_verifikasi === 'ditolak')
                Profil usaha ditolak. Perbarui data untuk mengajukan verifikasi ulang.
            @else
                Profil usaha menunggu verifikasi kader desa. Kamu bisa menyiapkan draf lowongan.
            @endif
        </p>
    @endif

    <div class="mt-6 mb-2 flex items-center justify-between gap-3">
        <h2 class="font-semibold">Lowongan</h2>
        <a href="{{ route('usaha.lowongan.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Buat lowongan</a>
    </div>
    <div class="bg-white rounded-xl border divide-y">
        @forelse ($usaha->lowongan as $l)
            <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-semibold">{{ $l->judul }}</p>
                    <p class="text-sm text-slate-600">
                        {{ \App\Models\Lowongan::JENIS_KERJA[$l->jenis_kerja] }} ·
                        @if ($l->status === 'aktif' && $l->penempatan_count >= $l->kuota)
                            Kuota terpenuhi
                        @elseif ($l->status === 'aktif' && $l->tanggal_tutup && $l->tanggal_tutup->toDateString() < \App\Models\Lowongan::tanggalDiPakis())
                            Lewat batas lamaran
                        @else
                            {{ \App\Models\Lowongan::STATUS[$l->status] }}
                        @endif
                        ·
                        {{ $l->lamaran_count }} pelamar
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($usaha->status_verifikasi === 'terverifikasi')
                        <a href="{{ route('usaha.pelamar.index', $l) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-blue-700" aria-label="Pelamar untuk {{ $l->judul }}">Pelamar</a>
                    @endif
                    <a href="{{ route('usaha.lowongan.edit', $l) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold" aria-label="Kelola {{ $l->judul }}">Kelola</a>
                </div>
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">Belum ada lowongan.</p>
        @endforelse
    </div>
@endif
@endsection
