@extends('layouts.app', ['judul' => 'Masuk'])
@section('konten')
<div class="max-w-sm mx-auto bg-white rounded-xl border border-slate-200 p-6">
    <h1 class="text-xl font-bold mb-4">Masuk ke Lokerin</h1>
    <form method="POST" action="{{ route('login') }}" class="space-y-3">@csrf
        <label class="block text-sm">Email atau nomor HP
            <input name="identitas" value="{{ old('identitas') }}" required class="mt-1 w-full rounded-lg border-slate-300">
        </label>
        <label class="block text-sm">Kata sandi
            <input type="password" name="password" required class="mt-1 w-full rounded-lg border-slate-300">
        </label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ingat"> Ingat saya</label>
        <button class="w-full py-2 rounded-lg bg-blue-600 text-white font-semibold">Masuk</button>
    </form>
    <p class="mt-4 text-sm text-slate-500">Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600">Daftar</a></p>
</div>
@endsection
