@extends('layouts.app', ['judul' => 'Tautan aktivasi warga'])

@section('konten')
<section class="card max-w-xl space-y-4 p-5">
    <h1 class="judul-halaman">Tautan aktivasi untuk {{ $warga->name }}</h1>
    <p class="text-sm text-slate-600">Serahkan tautan ini langsung ke warga. Warga membukanya di perangkat sendiri atau di jendela privat, lalu membuat kata sandi.</p>
    <p class="text-sm text-slate-600">Tautan hanya dapat dipakai sekali dan berlaku selama {{ $kedaluwarsaMenit }} menit. Jika perlu, buat tautan baru dari daftar warga.</p>
    <label for="tautan-aktivasi" class="block text-sm font-medium">Tautan aktivasi</label>
    <textarea id="tautan-aktivasi" rows="3" readonly class="block w-full text-sm">{{ $tautan }}</textarea>
    <div class="flex flex-wrap items-center gap-3">
        <button type="button" data-salin="tautan-aktivasi" data-salin-sukses="Tautan disalin." hidden class="btn btn-primary">Salin tautan</button>
        <a href="{{ route('kader.warga.index') }}" class="text-sm link">Kembali ke daftar warga</a>
    </div>
    <p id="tautan-aktivasi-status" role="status" class="text-sm text-slate-600"></p>
</section>
@endsection
