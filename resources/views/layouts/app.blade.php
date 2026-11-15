<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul ?? 'Lokerin' }} | Lokerin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<header class="bg-white border-b border-slate-200">
    <nav class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between gap-4">
        <a href="{{ route('beranda') }}" class="font-bold text-lg text-blue-600">Lokerin</a>
        <div class="flex items-center gap-4 text-sm overflow-x-auto">
            <a href="{{ route('lowongan.index') }}" class="hover:text-blue-600">Lowongan</a>
            @auth
                @switch(auth()->user()->role)
                    @case('pencari')
                        <a href="{{ route('pencari.beranda') }}" class="hover:text-blue-600">Rekomendasi</a>
                        <a href="{{ route('pencari.lamaran') }}" class="hover:text-blue-600">Lamaran</a>
                        <a href="{{ route('pencari.profil') }}" class="hover:text-blue-600">Profil</a>
                        @break
                    @case('usaha')
                        <a href="{{ route('usaha.beranda') }}" class="hover:text-blue-600">Usaha saya</a>
                        @break
                    @case('kader')
                        <a href="{{ route('kader.dasbor') }}" class="hover:text-blue-600">Dasbor desa</a>
                        <a href="{{ route('kader.verifikasi') }}" class="hover:text-blue-600">Verifikasi</a>
                        @break
                    @case('disnaker')
                        <a href="{{ route('disnaker.dasbor') }}" class="hover:text-blue-600">Dasbor</a>
                        @break
                @endswitch
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button class="text-slate-500 hover:text-red-600">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hover:text-blue-600">Masuk</a>
                <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white">Daftar</a>
            @endauth
        </div>
    </nav>
</header>

<main class="max-w-5xl mx-auto px-4 py-6">
    @foreach (['sukses' => 'bg-green-50 text-green-800 border-green-200', 'info' => 'bg-blue-50 text-blue-800 border-blue-200'] as $k => $kelas)
        @if (session($k))
            <div class="mb-4 p-3 rounded-lg border text-sm {{ $kelas }}">{{ session($k) }}</div>
        @endif
    @endforeach
    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg border border-red-200 bg-red-50 text-red-800 text-sm">
            <ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('konten')
</main>
<footer class="text-center text-xs text-slate-400 py-6">Lokerin, platform lowongan kerja lokal Kecamatan Pakis. Prototipe tugas Smart City 2026.</footer>
</body>
</html>
