@extends('layouts.app', ['judul' => 'Panduan AK/1'])

@section('konten')
<article class="card max-w-2xl space-y-5 p-5 sm:p-7">
    <header class="space-y-2">
        <h1 class="judul-halaman">Panduan kartu pencari kerja (AK/1)</h1>
        <p class="text-sm text-slate-600">Buku I Smart City Kecamatan Pakis mengusulkan Desa Pakiskembar sebagai simpul pendampingan warga dalam mengurus kartu pencari kerja.</p>
    </header>
    @include('partials.pemberitahuan-ak1')
    <section aria-labelledby="layanan-resmi-ak1" class="space-y-2 text-sm">
        <h2 id="layanan-resmi-ak1" class="text-base font-semibold">Layanan resmi</h2>
        <p>Untuk pengurusan dalam negeri, Dinas Tenaga Kerja Kabupaten Malang mengarahkan warga ke kantor kecamatan sesuai domisili. Siapkan salinan KTP yang berlaku, salinan ijazah terakhir, dan dua pasfoto berwarna terbaru ukuran 3 × 4 cm.</p>
        <p>Periksa ketentuan dengan petugas layanan sebelum berangkat. Untuk jalur daring, ikuti panduan Karirhub Kemnaker.</p>
        <ul class="space-y-2">
            <li><a href="https://disnaker.malangkab.go.id/" class="link">Informasi Dinas Tenaga Kerja Kabupaten Malang</a></li>
            <li><a href="https://pakis.malangkab.go.id/" class="link">Kontak Kecamatan Pakis</a></li>
            <li><a href="https://panduan.kemnaker.go.id/panduan/20" class="link">Panduan Karirhub Kemnaker</a></li>
        </ul>
    </section>
    @guest
        <a href="{{ route('ak1.index') }}" class="btn btn-primary inline-block">Masuk untuk membuka pendampingan</a>
    @else
        @if (auth()->user()->hasRole(\App\Models\User::ROLE_PENCARI) || (auth()->user()->hasRole(\App\Models\User::ROLE_KADER) && auth()->user()->desa?->simpulAk1()))
            <a href="{{ route('ak1.index') }}" class="btn btn-primary inline-block">Buka pendampingan AK/1</a>
        @endif
    @endguest
</article>
@endsection
