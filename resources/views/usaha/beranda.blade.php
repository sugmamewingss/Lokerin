@extends('layouts.app', ['judul' => 'Usaha saya'])
@section('konten')
<div class="card flex flex-wrap items-center justify-between gap-4 p-6">
    <div class="flex items-center gap-4">
    <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-amber-100 text-amber-700"><x-ikon nama="toko" class="h-7 w-7" /></span>
    <div>
        <p class="eyebrow">Usaha saya</p>
        <h1 class="judul-halaman mt-1">{{ $usaha?->nama_usaha ?? 'Usaha saya' }}</h1>
        @if ($usaha)
            <p class="text-sm text-slate-600">{{ $usaha->desa->nama }} · {{ ucfirst($usaha->status_verifikasi) }}@if ($usaha->status_verifikasi === 'menunggu') verifikasi @endif</p>
        @else
            <p class="text-sm text-slate-600">Lengkapi profil agar kader desa bisa memverifikasi usahamu.</p>
        @endif
    </div>
    </div>
    <a href="{{ route('usaha.profil') }}" class="btn btn-secondary">{{ $usaha ? 'Ubah profil' : 'Lengkapi profil' }}</a>
</div>

@if ($usaha)
    @if ($usaha->status_verifikasi !== 'terverifikasi')
        <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
            @if ($usaha->status_verifikasi === 'ditolak')
                Profil usaha ditolak. Perbarui data untuk mengajukan verifikasi ulang.
            @else
                Profil usaha menunggu verifikasi kader desa. Kamu bisa menyiapkan draf lowongan.
            @endif
        </p>
    @endif

    <div class="mb-3 mt-8 flex items-center justify-between gap-3">
        <h2 class="text-xl font-extrabold">Lowongan</h2>
        <a href="{{ route('usaha.lowongan.create') }}" class="btn btn-primary">Buat lowongan</a>
    </div>
    <div class="card divide-y">
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
                        <a href="{{ route('usaha.pelamar.index', $l) }}" class="btn btn-secondary px-3 py-2" aria-label="Pelamar untuk {{ $l->judul }}">Pelamar</a>
                    @endif
                    <a href="{{ route('usaha.lowongan.edit', $l) }}" class="btn btn-secondary px-3 py-2" aria-label="Kelola {{ $l->judul }}">Kelola</a>
                </div>
            </div>
        @empty
            <p class="p-4 text-sm text-slate-500">Belum ada lowongan.</p>
        @endforelse
    </div>
@endif
@endsection
