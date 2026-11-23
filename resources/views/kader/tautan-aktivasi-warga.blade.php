@extends('layouts.app', ['judul' => 'Tautan aktivasi warga'])

@section('konten')
<section class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-5">
    <h1 class="text-xl font-bold">Tautan aktivasi untuk {{ $warga->name }}</h1>
    <p class="text-sm text-slate-600">Serahkan tautan ini langsung ke warga. Warga membukanya di perangkat sendiri atau di jendela privat, lalu membuat kata sandi.</p>
    <p class="text-sm text-slate-600">Tautan hanya dapat dipakai sekali dan berlaku selama {{ $kedaluwarsaMenit }} menit. Jika perlu, buat tautan baru dari daftar warga.</p>
    <label for="tautan-aktivasi" class="block text-sm font-medium">Tautan aktivasi</label>
    <textarea id="tautan-aktivasi" rows="3" readonly class="block w-full rounded-lg border-slate-300 text-sm">{{ $tautan }}</textarea>
    <div class="flex flex-wrap items-center gap-3">
        <button type="button" data-salin="tautan-aktivasi" data-salin-sukses="Tautan disalin." hidden class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Salin tautan</button>
        <a href="{{ route('kader.warga.index') }}" class="text-sm font-medium text-blue-700 underline">Kembali ke daftar warga</a>
    </div>
    <p id="tautan-aktivasi-status" role="status" class="text-sm text-slate-600"></p>
</section>
@endsection
