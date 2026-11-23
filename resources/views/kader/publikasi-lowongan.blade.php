@extends('layouts.app', ['judul' => 'Bagikan lowongan', 'cetakDaftarLowongan' => true])

@section('konten')
<div class="mb-4 space-y-1">
    <h1 class="judul-halaman">Lowongan aktif {{ $wilayah }}</h1>
    <p class="text-sm text-slate-600">Disiapkan kader Desa {{ $desa->nama }} · {{ $waktu }}</p>
    <p class="text-sm text-slate-600">{{ $lowongan->count() }} lowongan tersedia saat daftar dibuat.</p>
</div>

<div class="mb-5 flex flex-wrap items-end justify-between gap-3 print:hidden">
    <form method="GET" action="{{ route('kader.publikasi-lowongan') }}" class="flex flex-wrap items-end gap-2 text-sm">
        <label class="block">Cakupan lowongan
            <select name="cakupan" class="mt-1 block">
                <option value="kecamatan" @selected(!$desaSaja)>Kecamatan {{ $desa->kecamatan }}</option>
                <option value="desa" @selected($desaSaja)>Desa {{ $desa->nama }}</option>
            </select>
        </label>
        <button class="btn btn-secondary px-3 py-2">Tampilkan</button>
    </form>
    @if ($lowongan->isNotEmpty())
        <button type="button" data-cetak-lowongan hidden class="btn btn-primary">Cetak untuk papan desa</button>
        <noscript><p class="text-sm text-slate-600">Gunakan menu Cetak dari peramban untuk mencetak daftar.</p></noscript>
    @endif
</div>

<section aria-labelledby="daftar-lowongan" class="mb-4">
    <h2 id="daftar-lowongan" class="sr-only">Daftar lowongan untuk papan desa</h2>
    <ul class="space-y-2">
        @forelse ($lowongan as $item)
            <li class="card baris-lowongan p-3 text-sm">
                <p class="font-semibold">{{ $item->judul }}</p>
                <p>{{ $item->usaha->nama_usaha }} · Desa {{ $item->usaha->desa->nama }}</p>
                <p class="text-slate-600">
                    {{ \App\Models\Lowongan::JENIS_KERJA[$item->jenis_kerja] }} · Pendidikan min. {{ $item->pendidikan_min }} ·
                    Batas melamar {{ $item->tanggal_tutup?->format('d/m/Y') ?? 'belum ditentukan' }}
                </p>
                <a href="{{ route('lowongan.detail', $item) }}" class="mt-1 inline-block print:hidden link" aria-label="Buka detail {{ $item->judul }}">Buka detail</a>
            </li>
        @empty
            <li class="card p-3 text-sm text-slate-600">Belum ada lowongan aktif untuk cakupan ini.</li>
        @endforelse
    </ul>
</section>

<p class="teks-katalog mb-5 text-sm text-slate-600">
    Cek status terbaru dan cara melamar di Lokerin atau minta bantuan kader desa.
    <a href="{{ $katalog }}" class="block break-all link">{{ $katalog }}</a>
</p>

@if ($lowongan->isNotEmpty())
    <section aria-labelledby="ringkasan-wa" class="card p-4 print:hidden">
        <h2 id="ringkasan-wa" class="font-semibold">Ringkasan untuk WhatsApp RW</h2>
        <label for="teks-lowongan" class="sr-only">Teks ringkasan lowongan</label>
        <textarea id="teks-lowongan" rows="9" readonly spellcheck="false" class="mt-3 block w-full text-sm">{{ $ringkasan }}</textarea>
        <button type="button" data-salin="teks-lowongan" data-salin-sukses="Ringkasan disalin." hidden class="btn btn-primary mt-3">Salin ringkasan</button>
        <p id="teks-lowongan-status" role="status" class="mt-2 text-sm text-slate-600"></p>
    </section>
@endif
@endsection
