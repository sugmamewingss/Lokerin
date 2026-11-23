@extends('layouts.app', ['judul' => $pelatihan->judul])

@section('konten')
<a href="{{ route('pelatihan.index') }}" class="mb-3 inline-block text-sm font-medium text-blue-700 underline">Kembali ke katalog</a>
<article class="rounded-xl border border-slate-200 bg-white p-5">
    <h1 class="text-xl font-bold">{{ $pelatihan->judul }}</h1>
    <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
        <div>
            <dt class="text-slate-600">Penyelenggara</dt>
            <dd>{{ $pelatihan->penyelenggara }}</dd>
        </div>
        @if ($pelatihan->jenis_penyelenggara)
            <div>
                <dt class="text-slate-600">Jenis penyelenggara</dt>
                <dd>{{ \App\Models\Pelatihan::JENIS_PENYELENGGARA[$pelatihan->jenis_penyelenggara] }}</dd>
            </div>
        @endif
        <div>
            <dt class="text-slate-600">Keahlian yang dipelajari</dt>
            <dd>{{ $pelatihan->keahlian->nama }}</dd>
        </div>
        <div>
            <dt class="text-slate-600">Jadwal</dt>
            <dd>{{ $pelatihan->jadwal ?? 'Belum diumumkan' }}</dd>
        </div>
        <div>
            <dt class="text-slate-600">Lokasi</dt>
            <dd>
                {{ $pelatihan->lokasi ?? ($pelatihan->desa ? 'Desa '.$pelatihan->desa->nama : 'Belum diumumkan') }}
                @if ($pelatihan->lokasi && $pelatihan->desa)
                    <span class="block text-slate-600">Desa {{ $pelatihan->desa->nama }} · {{ $pelatihan->desa->kecamatan }}</span>
                @endif
            </dd>
        </div>
        @if ($pelatihan->kuota !== null)
            <div>
                <dt class="text-slate-600">Kuota pelatihan</dt>
                <dd>{{ $pelatihan->kuota }} peserta</dd>
            </div>
        @endif
    </dl>
</article>

@include('partials.informasi-banjir', ['desaLokasi' => $pelatihan->desa])

<section class="mt-6" aria-labelledby="lowongan-terkait">
    <h2 id="lowongan-terkait" class="mb-3 text-lg font-semibold">Lowongan yang membutuhkan keahlian ini</h2>
    <div class="grid gap-3 sm:grid-cols-2">
        @forelse ($lowongan as $l)
            @include('partials.kartu-lowongan', ['l' => $l])
        @empty
            <p class="text-sm text-slate-600">Belum ada lowongan terbuka yang mewajibkan keahlian ini.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $lowongan->links() }}</div>
</section>
@endsection
