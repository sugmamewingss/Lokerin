@extends('layouts.app', ['judul' => 'Aktivasi akun warga'])

@section('konten')
<form action="{{ route('aktivasi-warga.store', $warga) }}" method="POST" class="mx-auto max-w-md space-y-4 rounded-xl border border-slate-200 bg-white p-5">
    @csrf
    <h1 class="text-xl font-bold">Aktivasi akun warga</h1>
    <p class="text-sm text-slate-600">Buat kata sandimu sendiri. Setelah akun aktif, kamu bisa melengkapi profil dan memilih persetujuan penggunaan data.</p>
    <noscript><p class="text-sm text-slate-600">Salin kode setelah tanda # di tautan yang diberikan kader ke kolom kode aktivasi.</p></noscript>
    <label class="block text-sm font-medium">Kode aktivasi
        <input type="password" name="kode_aktivasi" required maxlength="64" autocomplete="off" data-kode-aktivasi class="mt-1 block w-full rounded-lg border-slate-300">
    </label>
    <label class="block text-sm font-medium">Kata sandi baru
        <input type="password" name="password" required minlength="8" autocomplete="new-password" class="mt-1 block w-full rounded-lg border-slate-300">
    </label>
    <label class="block text-sm font-medium">Ulangi kata sandi baru
        <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" class="mt-1 block w-full rounded-lg border-slate-300">
    </label>
    <button class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Aktifkan akun</button>
</form>
@endsection
