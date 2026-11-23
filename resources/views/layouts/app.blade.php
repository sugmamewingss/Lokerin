<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1c7e52">
    <title>{{ $judul ?? 'Lokerin' }} | Lokerin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $pengguna = auth()->user();
    $menuUmum = [
        ['lowongan.index', 'Lowongan', 'lowongan.*'],
        ['pelatihan.index', 'Pelatihan', 'pelatihan.*'],
    ];
    $menuPeran = match ($pengguna?->role) {
        'pencari' => [
            ['pencari.beranda', 'Rekomendasi', 'pencari.beranda'],
            ['pencari.lamaran', 'Lamaran', 'pencari.lamaran'],
            ['ak1.index', 'AK/1', 'ak1.*'],
            ['pencari.profil', 'Profil', 'pencari.profil'],
        ],
        'usaha' => [
            ['usaha.beranda', 'Usaha saya', 'usaha.*'],
        ],
        'kader' => [
            ['kader.dasbor', 'Dasbor desa', 'kader.dasbor'],
            ['kader.warga.index', 'Daftarkan warga', 'kader.warga.*'],
            ['kader.verifikasi', 'Verifikasi', 'kader.verifikasi*'],
        ],
        'disnaker' => [
            ['disnaker.dasbor', 'Dasbor', 'disnaker.dasbor'],
            ['disnaker.pelatihan.index', 'Kelola pelatihan', 'disnaker.pelatihan.*'],
        ],
        default => [],
    };
    $menu = array_merge($menuUmum, $menuPeran);
    $labelPeran = ['pencari' => 'Pencari kerja', 'usaha' => 'Pemilik usaha', 'kader' => 'Kader desa', 'disnaker' => 'Disnaker'][$pengguna?->role] ?? null;
    $inisial = $pengguna ? mb_strtoupper(mb_substr(trim($pengguna->name), 0, 1)) : '';
@endphp
<body @class(['min-h-screen flex flex-col bg-slate-50 text-slate-700 antialiased', 'cetak-daftar-lowongan' => $cetakDaftarLowongan ?? false])>
<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/85 backdrop-blur-md">
    <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6" aria-label="Navigasi utama">
        <a href="{{ route('beranda') }}" class="group flex shrink-0 items-center gap-2.5">
            <x-logo class="h-9 w-9 transition group-hover:-rotate-6" />
            <span class="leading-tight">
                <span class="block text-lg font-extrabold tracking-tight text-slate-900">Lokerin</span>
                <span class="hidden text-[11px] font-medium text-slate-500 sm:block">Kerja dekat rumah · Kec. Pakis</span>
            </span>
        </a>

        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($menu as [$rute, $label, $pola])
                <a href="{{ route($rute) }}" @class(['nav-link whitespace-nowrap', 'nav-link-aktif' => request()->routeIs($pola)]) @if (request()->routeIs($pola)) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </div>

        <div class="hidden items-center gap-2 lg:flex">
            @auth
                <div class="flex items-center gap-2.5 rounded-full border border-slate-200 bg-white py-1 pl-1 pr-3">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-800">{{ $inisial }}</span>
                    <span class="max-w-[10rem] leading-tight">
                        <span class="block truncate text-sm font-semibold text-slate-800">{{ $pengguna->name }}</span>
                        <span class="block text-[11px] text-slate-500">{{ $labelPeran }}</span>
                    </span>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="btn btn-ghost px-3" title="Keluar"><x-ikon nama="keluar" /><span>Keluar</span></button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
            @endauth
        </div>

        <details class="group relative lg:hidden">
            <summary class="btn btn-secondary cursor-pointer list-none px-3 [&::-webkit-details-marker]:hidden" aria-label="Buka menu">
                <x-ikon nama="menu" class="h-5 w-5 group-open:hidden" />
                <x-ikon nama="tutup" class="hidden h-5 w-5 group-open:block" />
            </summary>
            <div class="card absolute right-0 mt-2 w-72 space-y-1 p-2">
                @auth
                    <div class="mb-1 flex items-center gap-3 rounded-xl bg-slate-50 p-3">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-100 font-bold text-brand-800">{{ $inisial }}</span>
                        <span class="leading-tight">
                            <span class="block text-sm font-semibold text-slate-800">{{ $pengguna->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $labelPeran }}</span>
                        </span>
                    </div>
                @endauth
                @foreach ($menu as [$rute, $label, $pola])
                    <a href="{{ route($rute) }}" @class(['nav-link block', 'nav-link-aktif' => request()->routeIs($pola)])>{{ $label }}</a>
                @endforeach
                <div class="border-t border-slate-100 pt-2">
                    @auth
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="nav-link flex w-full items-center gap-2 text-red-600 hover:bg-red-50 hover:text-red-700"><x-ikon nama="keluar" />Keluar</button>
                        </form>
                    @else
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('login') }}" class="btn btn-secondary">Masuk</a>
                            <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                        </div>
                    @endauth
                </div>
            </div>
        </details>
    </nav>
</header>

<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">
    @foreach (['sukses' => ['border-brand-200 bg-brand-50 text-brand-900', 'centang', 'text-brand-600'], 'info' => ['border-sky-200 bg-sky-50 text-sky-900', 'info', 'text-sky-600']] as $k => [$kelas, $ikon, $warnaIkon])
        @if (session($k))
            <div data-pesan-lokerin class="mb-6 flex items-start gap-3 rounded-2xl border p-4 text-sm {{ $kelas }}">
                <x-ikon :nama="$ikon" class="h-5 w-5 shrink-0 {{ $warnaIkon }}" />
                <p>{{ session($k) }}</p>
            </div>
        @endif
    @endforeach
    @if ($errors->any())
        <div data-pesan-lokerin class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <x-ikon nama="peringatan" class="h-5 w-5 shrink-0 text-red-600" />
            <ul class="list-disc space-y-0.5 pl-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('konten')
</main>

<footer class="mt-12 border-t border-slate-200 bg-white">
    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
        <div class="space-y-3">
            <div class="flex items-center gap-2.5">
                <x-logo class="h-8 w-8" />
                <span class="text-lg font-extrabold tracking-tight text-slate-900">Lokerin</span>
            </div>
            <p class="max-w-sm text-sm text-slate-600">Lokerin, platform lowongan kerja lokal Kecamatan Pakis. Prototipe tugas Smart City 2026.</p>
            <p class="text-xs text-slate-500">Seluruh nama orang dan usaha pada data demo bersifat fiktif.</p>
        </div>
        <div>
            <h2 class="text-sm font-bold text-slate-900">Jelajahi</h2>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('lowongan.index') }}" class="text-slate-600 hover:text-brand-700">Lowongan kerja</a></li>
                <li><a href="{{ route('pelatihan.index') }}" class="text-slate-600 hover:text-brand-700">Katalog pelatihan</a></li>
                @guest<li><a href="{{ route('register') }}" class="text-slate-600 hover:text-brand-700">Daftar akun</a></li>@endguest
            </ul>
        </div>
        <div>
            <h2 class="text-sm font-bold text-slate-900">Informasi</h2>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('privasi') }}" class="text-slate-600 hover:text-brand-700">Kebijakan data</a></li>
                <li><a href="{{ route('ak1.panduan') }}" class="text-slate-600 hover:text-brand-700">Panduan AK/1</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-slate-100 py-4 text-center text-xs text-slate-500">© 2026 Lokerin · Kecamatan Pakis, Kabupaten Malang</div>
</footer>
</body>
</html>
