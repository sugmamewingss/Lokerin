@extends('layouts.app', ['judul' => 'Masuk'])
@section('konten')
<div class="card mx-auto grid max-w-4xl overflow-hidden lg:grid-cols-2">
    @include('partials.panel-auth', [
        'judulPanel' => 'Selamat datang kembali di Lokerin.',
        'poinPanel' => ['Rekomendasi lowongan sesuai jurusan', 'Pantau status lamaran kapan saja', 'Masuk dengan email atau nomor HP'],
    ])
    <div class="p-6 sm:p-10">
        <h1 class="text-2xl font-extrabold">Masuk ke Lokerin</h1>
        <p class="mt-1 text-sm text-slate-500">Gunakan akun yang sudah terdaftar.</p>
        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">@csrf
            <label class="block text-sm font-medium">Email atau nomor HP
                <input name="identitas" value="{{ old('identitas') }}" required autocomplete="username" class="mt-1.5 w-full">
            </label>
            <label class="block text-sm font-medium">Kata sandi
                <input type="password" name="password" required autocomplete="current-password" class="mt-1.5 w-full">
            </label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ingat"> Ingat saya</label>
            <button class="btn btn-primary w-full py-3">Masuk</button>
        </form>
        <p class="mt-6 text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="link">Daftar</a></p>
    </div>
</div>
@endsection
