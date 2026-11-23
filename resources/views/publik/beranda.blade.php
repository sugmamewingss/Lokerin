@extends('layouts.app', ['judul' => 'Beranda'])
@section('konten')
{{-- Hero --}}
<section class="relative overflow-hidden rounded-[2rem] bg-brand-950 px-6 py-12 text-white shadow-angkat sm:px-10 sm:py-16 lg:py-20">
    <div class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image: radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0); background-size: 22px 22px;"></div>
    <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-amber-400/10 blur-3xl"></div>

    <div class="relative grid items-center gap-12 lg:grid-cols-[1.15fr_1fr]">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-brand-100">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                Untuk warga {{ $jumlahDesa }} desa di Kecamatan Pakis
            </span>
            <h1 class="mt-5 text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-5xl">
                Kerja dekat rumah, <span class="text-brand-300">sesuai jurusanmu.</span>
            </h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-brand-100/90 sm:text-lg">Lokerin mempertemukan warga Kecamatan Pakis dengan lowongan dari UMKM dan usaha di sekitarnya.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="btn bg-white px-5 py-3 text-brand-900 shadow-lg shadow-black/10 hover:bg-brand-50">Daftar gratis <x-ikon nama="panah-kanan" class="h-4 w-4" /></a>
                <a href="{{ route('lowongan.index') }}" class="btn border border-white/25 px-5 py-3 text-white hover:bg-white/10">Lihat {{ $jumlahLowongan }} lowongan</a>
            </div>
            <dl class="mt-10 grid max-w-md grid-cols-3 gap-4 border-t border-white/10 pt-6">
                <div><dt class="text-xs text-brand-200/80">Lowongan aktif</dt><dd class="mt-1 text-2xl font-extrabold tabular-nums">{{ $jumlahLowongan }}</dd></div>
                <div><dt class="text-xs text-brand-200/80">Usaha terverifikasi</dt><dd class="mt-1 text-2xl font-extrabold tabular-nums">{{ $jumlahUsaha }}</dd></div>
                <div><dt class="text-xs text-brand-200/80">Desa terjangkau</dt><dd class="mt-1 text-2xl font-extrabold tabular-nums">{{ $jumlahDesa }}</dd></div>
            </dl>
        </div>

        {{-- Ilustrasi: lowongan tersebar di desa-desa sekitar --}}
        <div class="relative mx-auto hidden aspect-square w-full max-w-md lg:block" aria-hidden="true">
            <div class="absolute inset-0 rounded-full border border-white/10"></div>
            <div class="absolute inset-[14%] rounded-full border border-white/10"></div>
            <div class="absolute inset-[30%] rounded-full border border-dashed border-white/15"></div>
            <div class="absolute inset-[42%] grid place-items-center rounded-full bg-brand-500/25 ring-8 ring-brand-500/10">
                <x-ikon nama="lokasi" class="h-9 w-9 text-brand-200" />
            </div>
            @php $posisi = [['8%', '18%'], ['62%', '6%'], ['74%', '52%'], ['46%', '80%'], ['2%', '62%'], ['30%', '34%']]; @endphp
            @foreach ($desaTeramai as $i => $d)
                @php [$kiri, $atas] = $posisi[$i] ?? ['40%', '40%']; @endphp
                <div class="absolute flex items-center gap-2 rounded-2xl border border-white/10 bg-white/95 px-3 py-2 text-slate-800 shadow-xl shadow-black/20" style="left: {{ $kiri }}; top: {{ $atas }};">
                    <span class="grid h-7 w-7 place-items-center rounded-lg {{ $d->lowongan_count ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-500' }}"><x-ikon nama="lokasi" class="h-4 w-4" /></span>
                    <span class="leading-tight">
                        <span class="block text-xs font-bold">{{ $d->nama }}</span>
                        <span class="block text-[11px] text-slate-500">{{ $d->lowongan_count }} lowongan</span>
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Cara kerja --}}
<section class="mt-16">
    <div class="max-w-2xl">
        <p class="eyebrow">Cara kerja</p>
        <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Satu tempat untuk pencari kerja, usaha, dan desa</h2>
    </div>
    <div class="mt-8 grid gap-4 md:grid-cols-3">
        @foreach ([
            ['profil', 'Isi profil sekali', 'Pendidikan, jurusan, dan keahlianmu dipakai untuk mencocokkan lowongan secara otomatis.'],
            ['kilau', 'Dapat rekomendasi', 'Lowongan diurutkan dari yang paling cocok atau paling dekat dengan desamu, lengkap dengan saran pelatihan.'],
            ['centang', 'Lamar dan pantau', 'Kirim lamaran dalam satu klik, lalu pantau statusnya sampai diterima bekerja.'],
        ] as $no => [$ikon, $judul, $teks])
            <article class="card group p-6 transition hover:-translate-y-0.5 hover:shadow-angkat">
                <div class="flex items-center justify-between">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100"><x-ikon :nama="$ikon" class="h-6 w-6" /></span>
                    <span class="text-4xl font-extrabold text-slate-100">0{{ $no + 1 }}</span>
                </div>
                <h3 class="mt-5 text-lg font-bold">{{ $judul }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $teks }}</p>
            </article>
        @endforeach
    </div>
</section>

{{-- Lowongan terbaru --}}
<section class="mt-16">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Baru dibuka</p>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Lowongan terbaru</h2>
        </div>
        <a href="{{ route('lowongan.index') }}" class="btn btn-secondary">Semua lowongan <x-ikon nama="panah-kanan" class="h-4 w-4" /></a>
    </div>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($terbaru as $l)
            @include('partials.kartu-lowongan', ['l' => $l])
        @empty
            <p class="card p-6 text-sm text-slate-500 sm:col-span-2 lg:col-span-3">Belum ada lowongan.</p>
        @endforelse
    </div>
</section>

{{-- Untuk usaha dan desa --}}
<section class="mt-16 grid gap-4 lg:grid-cols-2">
    <article class="card relative overflow-hidden p-8">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-amber-100"></div>
        <span class="relative grid h-12 w-12 place-items-center rounded-xl bg-amber-400 text-amber-950"><x-ikon nama="toko" class="h-6 w-6" /></span>
        <h2 class="relative mt-5 text-xl font-extrabold">Punya usaha di Pakis?</h2>
        <p class="relative mt-2 max-w-md text-sm leading-relaxed text-slate-600">Pasang lowongan penuh waktu maupun paruh waktu, terima pelamar dari desa sekitar, dan catat penempatan kerja dengan mudah.</p>
        <a href="{{ route('register') }}" class="btn btn-secondary relative mt-6">Daftar sebagai usaha</a>
    </article>
    <article class="card relative overflow-hidden p-8">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-brand-100"></div>
        <span class="relative grid h-12 w-12 place-items-center rounded-xl bg-brand-600 text-white"><x-ikon nama="perisai" class="h-6 w-6" /></span>
        <h2 class="relative mt-5 text-xl font-extrabold">Didampingi kader desa</h2>
        <p class="relative mt-2 max-w-md text-sm leading-relaxed text-slate-600">Kader desa memverifikasi usaha, membantu warga mendaftar, dan memantau rekap ketenagakerjaan desanya.</p>
        <a href="{{ route('privasi') }}" class="btn btn-secondary relative mt-6">Cara kami menjaga data</a>
    </article>
</section>

{{-- Lowongan per desa --}}
@if ($desaTeramai->sum('lowongan_count') > 0)
    <section class="mt-16">
        <p class="eyebrow">Jelajahi per desa</p>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($desaTeramai->where('lowongan_count', '>', 0) as $d)
                <a href="{{ route('lowongan.index', ['desa' => $d->id]) }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-brand-300 hover:text-brand-800">
                    <x-ikon nama="lokasi" class="h-4 w-4 text-brand-600" /> {{ $d->nama }}
                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700">{{ $d->lowongan_count }}</span>
                </a>
            @endforeach
        </div>
    </section>
@endif
@endsection
