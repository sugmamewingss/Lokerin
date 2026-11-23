@extends('layouts.app', ['judul' => 'Persiapan integrasi SIAPkerja'])

@section('konten')
<div class="space-y-5">
    <header class="space-y-2">
        <a href="{{ route('disnaker.dasbor') }}" class="inline-block text-sm font-medium text-blue-700 underline">Kembali ke dasbor</a>
        <h1 class="text-xl font-bold">Persiapan integrasi SIAPkerja</h1>
        <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
            Pertukaran data menunggu kontrak API dan akses uji dari Kemnaker. Halaman ini menampilkan pratinjau lokal.
        </p>
    </header>
    <section aria-labelledby="lowongan-siapkerja" class="space-y-3">
        <h2 id="lowongan-siapkerja" class="text-lg font-semibold">Lowongan publik Kecamatan Pakis</h2>
        <p class="text-sm text-slate-600">Tinjau lowongan dari usaha terverifikasi yang masih menerima lamaran.</p>
        <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
            @forelse ($lowongan as $item)
                <li class="space-y-1 p-4 text-sm">
                    <a href="{{ route('lowongan.detail', $item) }}" class="break-words font-semibold text-blue-700 underline">{{ $item->judul }}</a>
                    <p class="break-words">{{ $item->usaha->nama_usaha }} · Desa {{ $item->usaha->desa->nama }}</p>
                    <p class="text-slate-600">
                        {{ \App\Models\Lowongan::JENIS_KERJA[$item->jenis_kerja] }}
                        · Pendidikan min. {{ $item->pendidikan_min }}
                        · Batas melamar {{ $item->tanggal_tutup?->format('d/m/Y') ?? 'belum ditentukan' }}
                    </p>
                </li>
            @empty
                <li class="p-4 text-sm text-slate-600">Belum ada lowongan publik yang masih menerima lamaran di Pakis.</li>
            @endforelse
        </ul>
        {{ $lowongan->links() }}
    </section>
    <p class="flex flex-wrap gap-x-4 gap-y-2 text-sm">
        <a href="https://jdih.kemnaker.go.id/asset/data_puu/2024pmnaker017.pdf" class="text-blue-700 underline">Aturan interoperabilitas SIAPkerja</a>
        <a href="https://paskerid.kemnaker.go.id/news/86" class="text-blue-700 underline">Skema kemitraan Karirhub</a>
    </p>
</div>
@endsection
