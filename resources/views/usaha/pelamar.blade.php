@extends('layouts.app', ['judul' => 'Pelamar'])

@section('konten')
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold">Pelamar {{ $lowongan->judul }}</h1>
        <p class="text-sm text-slate-600">{{ $pelamar->total() }} lamaran · {{ $sisaKuota }} dari {{ $lowongan->kuota }} slot penempatan tersisa</p>
    </div>
    <a href="{{ route('usaha.beranda') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold">Kembali ke usaha saya</a>
</div>

<div class="space-y-3">
    @forelse ($pelamar as $lamaran)
        @php
            $aksi = route('usaha.pelamar.update', ['lowongan' => $lowongan, 'lamaran' => $lamaran, 'page' => $pelamar->currentPage()]);
            $formAktif = old('form_lamaran') == $lamaran->id;
        @endphp
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="font-semibold">{{ $lamaran->profil->user->name }}</h2>
                    <p class="text-sm text-slate-600">{{ $lamaran->profil->pendidikan_terakhir }} {{ $lamaran->profil->jurusan }} · Skor {{ $lamaran->skor_cocok }}%</p>
                </div>
                <span class="rounded bg-slate-100 px-2 py-1 text-sm font-medium">{{ \App\Models\Lamaran::STATUS[$lamaran->status] }}</span>
            </div>
            @if ($lamaran->kontakTerbuka())
                <p class="mt-2 text-sm">Nomor HP {{ $lamaran->profil->user->no_hp ?? 'belum tersedia' }}</p>
            @endif
            @if ($lamaran->status === 'diterima' && $lamaran->penempatan)
                @include('partials.ringkasan-penempatan', ['penempatan' => $lamaran->penempatan])
            @endif

            <div class="mt-3 flex flex-wrap gap-2">
                @foreach (\App\Models\Lamaran::AKSI_USAHA as $status => $label)
                    @continue($status === 'diterima' || !$lamaran->bolehPindahKe($status))
                    <form method="POST" action="{{ $aksi }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $status }}">
                        <button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium {{ $status === 'ditolak' ? 'text-red-700' : 'text-blue-700' }}">{{ $label }}</button>
                    </form>
                @endforeach
            </div>

            @if ($lamaran->bolehPindahKe('diterima') && $sisaKuota > 0)
                <details class="mt-3 rounded-lg border border-slate-200 p-3" @if ($formAktif) open @endif>
                    <summary class="cursor-pointer text-sm font-semibold text-blue-700">Terima dan catat penempatan</summary>
                    <form method="POST" action="{{ $aksi }}" class="mt-3">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="diterima">
                        <input type="hidden" name="form_lamaran" value="{{ $lamaran->id }}">
                        <div class="grid gap-3 text-sm sm:grid-cols-3">
                            <label>Tanggal mulai kerja
                                <input type="date" name="tanggal_mulai" value="{{ $formAktif ? old('tanggal_mulai') : '' }}" required class="mt-1 block w-full rounded-lg border-slate-300">
                            </label>
                            <label>Jenis kontrak
                                <select name="jenis_kontrak" required class="mt-1 block w-full rounded-lg border-slate-300">
                                    <option value="">Pilih jenis kontrak</option>
                                    @foreach (\App\Models\Penempatan::JENIS_KONTRAK as $jenis => $label)
                                        <option value="{{ $jenis }}" @selected($formAktif && old('jenis_kontrak') === $jenis)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Kepesertaan BPJS
                                <select name="terdaftar_bpjs" required class="mt-1 block w-full rounded-lg border-slate-300">
                                    <option value="">Pilih status</option>
                                    <option value="1" @selected($formAktif && old('terdaftar_bpjs') === '1')>Terdaftar</option>
                                    <option value="0" @selected($formAktif && old('terdaftar_bpjs') === '0')>Belum terdaftar</option>
                                </select>
                            </label>
                        </div>
                        <button class="mt-3 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Terima pelamar</button>
                    </form>
                </details>
            @endif
        </article>
    @empty
        <p class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600">Belum ada lamaran yang bisa ditinjau.</p>
    @endforelse
</div>
<div class="mt-4">{{ $pelamar->links() }}</div>
@endsection
