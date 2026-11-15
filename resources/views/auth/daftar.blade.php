@extends('layouts.app', ['judul' => 'Daftar'])
@section('konten')
<div class="max-w-md mx-auto bg-white rounded-xl border border-slate-200 p-6">
    <h1 class="text-xl font-bold mb-4">Daftar akun</h1>
    <form method="POST" action="{{ route('register') }}" class="space-y-3">@csrf
        <fieldset class="grid grid-cols-2 gap-2 text-sm">
            <label class="border rounded-lg p-3 cursor-pointer has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                <input type="radio" name="role" value="pencari" @checked(old('role', 'pencari') === 'pencari')> Saya mencari kerja
            </label>
            <label class="border rounded-lg p-3 cursor-pointer has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                <input type="radio" name="role" value="usaha" @checked(old('role') === 'usaha')> Saya pemilik usaha
            </label>
        </fieldset>
        <label class="block text-sm">Nama lengkap <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-slate-300"></label>
        <label class="block text-sm">Nomor HP <input name="no_hp" value="{{ old('no_hp') }}" required class="mt-1 w-full rounded-lg border-slate-300"></label>
        <label class="block text-sm">Email (opsional) <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
        <label class="block text-sm">Desa
            <select name="desa_id" required class="mt-1 w-full rounded-lg border-slate-300">
                @foreach ($desa as $d)<option value="{{ $d->id }}" @selected(old('desa_id') == $d->id)>{{ $d->nama }}</option>@endforeach
            </select>
        </label>
        <label class="block text-sm">Kata sandi <input type="password" name="password" required class="mt-1 w-full rounded-lg border-slate-300"></label>
        <label class="block text-sm">Ulangi kata sandi <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-lg border-slate-300"></label>
        <button class="w-full py-2 rounded-lg bg-blue-600 text-white font-semibold">Daftar</button>
    </form>
</div>
@endsection
