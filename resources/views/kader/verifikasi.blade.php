@extends('layouts.app', ['judul' => 'Verifikasi usaha'])
@section('konten')
<h1 class="text-xl font-bold mb-4">Verifikasi usaha di desa</h1>
<div class="bg-white rounded-xl border divide-y">
    @forelse ($usaha as $u)
        <div class="p-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="font-semibold">{{ $u->nama_usaha }}</p>
                <p class="text-sm text-slate-500">{{ $u->jenis_usaha }} · {{ $u->alamat }} · pemilik {{ $u->user->name }}</p>
                <p class="text-sm text-slate-600">{{ $u->is_umkm === null ? 'Skala usaha belum ditentukan' : ($u->is_umkm ? 'UMKM' : 'Perusahaan non-UMKM') }}</p>
            </div>
            @if ($u->status_verifikasi === 'menunggu')
                <div class="flex gap-2">
                    @foreach (['terverifikasi' => 'bg-blue-600 text-white', 'ditolak' => 'border text-red-600'] as $s => $kelas)
                        <form method="POST" action="{{ route('kader.verifikasi.update', $u) }}">@csrf
                            <input type="hidden" name="status" value="{{ $s }}">
                            <input type="hidden" name="versi_profil" value="{{ $u->versiVerifikasi() }}">
                            <button class="px-3 py-1.5 rounded-lg text-sm {{ $kelas }}">{{ $s === 'terverifikasi' ? 'Verifikasi' : 'Tolak' }}</button>
                        </form>
                    @endforeach
                </div>
            @else
                <span class="text-xs px-2 py-1 rounded bg-slate-100">{{ $u->status_verifikasi }}</span>
            @endif
        </div>
    @empty
        <p class="p-4 text-sm text-slate-500">Belum ada usaha terdaftar di desa ini.</p>
    @endforelse
</div>
@endsection
