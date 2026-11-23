@extends('layouts.app', ['judul' => 'Daftar'])
@section('konten')
<div class="card mx-auto grid max-w-5xl overflow-hidden lg:grid-cols-[0.9fr_1.1fr]">
    @include('partials.panel-auth', [
        'judulPanel' => 'Mulai dari desamu sendiri.',
        'poinPanel' => ['Gratis untuk warga dan UMKM Kecamatan Pakis', 'Usaha diverifikasi oleh kader desa', 'Data pribadi hanya dipakai dengan persetujuanmu'],
    ])
    <div class="p-6 sm:p-10">
        <h1 class="text-2xl font-extrabold">Daftar akun</h1>
        <p class="mt-1 text-sm text-slate-500">Pilih peranmu, lalu lengkapi data singkat berikut.</p>
        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">@csrf
            <fieldset class="grid grid-cols-2 gap-3 text-sm">
                <label class="flex cursor-pointer flex-col gap-2 rounded-2xl border border-slate-200 p-4 transition hover:border-brand-300 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:checked]:ring-4 has-[:checked]:ring-brand-500/10">
                    <span class="flex items-center justify-between"><x-ikon nama="koper" class="h-6 w-6 text-brand-700" /><input type="radio" name="role" value="pencari" @checked(old('role', 'pencari') === 'pencari')></span>
                    <span class="font-semibold text-slate-800">Saya mencari kerja</span>
                </label>
                <label class="flex cursor-pointer flex-col gap-2 rounded-2xl border border-slate-200 p-4 transition hover:border-brand-300 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:checked]:ring-4 has-[:checked]:ring-brand-500/10">
                    <span class="flex items-center justify-between"><x-ikon nama="toko" class="h-6 w-6 text-amber-600" /><input type="radio" name="role" value="usaha" @checked(old('role') === 'usaha')></span>
                    <span class="font-semibold text-slate-800">Saya pemilik usaha</span>
                </label>
            </fieldset>
            <label class="block text-sm font-medium">Nama lengkap <input name="name" value="{{ old('name') }}" required autocomplete="name" class="mt-1.5 w-full"></label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium">Nomor HP <input name="no_hp" value="{{ old('no_hp') }}" required inputmode="tel" class="mt-1.5 w-full"></label>
                <label class="block text-sm font-medium">Email (opsional) <input type="email" name="email" value="{{ old('email') }}" class="mt-1.5 w-full"></label>
            </div>
            <label class="block text-sm font-medium">Desa
                <select name="desa_id" required class="mt-1.5 w-full">
                    @foreach ($desa as $d)<option value="{{ $d->id }}" @selected(old('desa_id') == $d->id)>{{ $d->nama }}</option>@endforeach
                </select>
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium">Kata sandi <input type="password" name="password" required autocomplete="new-password" class="mt-1.5 w-full"></label>
                <label class="block text-sm font-medium">Ulangi kata sandi <input type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1.5 w-full"></label>
            </div>
            <button class="btn btn-primary w-full py-3">Daftar</button>
        </form>
        <p class="mt-6 text-sm text-slate-500">Sudah punya akun? <a href="{{ route('login') }}" class="link">Masuk</a></p>
    </div>
</div>
@endsection
