@extends('layouts.app', ['judul' => 'Profil'])
@section('konten')
<form method="POST" action="{{ route('pencari.profil') }}" class="card p-5 space-y-4 max-w-2xl">
    @csrf @method('PUT')
    <h1 class="judul-halaman">Profil dan keahlian</h1>
    <div class="grid sm:grid-cols-2 gap-3 text-sm">
        <label>Desa
            <select name="desa_id" class="mt-1 w-full">
                @foreach ($desa as $d)<option value="{{ $d->id }}" @selected(old('desa_id', $user->desa_id) == $d->id)>{{ $d->nama }}</option>@endforeach
            </select>
        </label>
        <label>Tanggal lahir <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $profil->tanggal_lahir?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
        <label>Jenis kelamin
            <select name="jenis_kelamin" class="mt-1 w-full">
                <option value="">-</option>
                <option value="L" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'P')>Perempuan</option>
            </select>
        </label>
        <label>Pendidikan terakhir
            <select name="pendidikan_terakhir" required class="mt-1 w-full">
                <option value="">Pilih pendidikan</option>
                @foreach (array_keys(config('lokerin.level_pendidikan')) as $p)<option @selected(old('pendidikan_terakhir', $profil->pendidikan_terakhir) === $p)>{{ $p }}</option>@endforeach
            </select>
        </label>
        <label>Jurusan
            <input list="jurusan-list" name="jurusan" value="{{ old('jurusan', $profil->jurusan) }}" class="mt-1 w-full rounded-lg border-slate-300">
            <datalist id="jurusan-list">@foreach ($jurusanList as $j)<option value="{{ $j }}">@endforeach</datalist>
        </label>
        <label>Status kerja
            <select name="status_kerja" class="mt-1 w-full">
                @foreach (['belum_bekerja' => 'Belum bekerja', 'paruh_waktu' => 'Bekerja paruh waktu', 'bekerja' => 'Sudah bekerja'] as $v => $t)
                    <option value="{{ $v }}" @selected(old('status_kerja', $profil->status_kerja) === $v)>{{ $t }}</option>
                @endforeach
            </select>
        </label>
    </div>
    <label class="flex items-center gap-2 text-sm"><input type="hidden" name="bersedia_paruh_waktu" value="0">
        <input type="checkbox" name="bersedia_paruh_waktu" value="1" @checked(old('bersedia_paruh_waktu', $profil->bersedia_paruh_waktu))> Bersedia kerja paruh waktu</label>

    <div>
        <p class="text-sm font-semibold mb-2">Keahlian</p>
        @php $punya = old('keahlian', $profil->keahlian->pluck('id')->all()); @endphp
        @foreach ($keahlian as $kategori => $daftar)
            <p class="text-xs text-slate-500 mt-2">{{ $kategori }}</p>
            <div class="flex flex-wrap gap-2 mt-1">
                @foreach ($daftar as $k)
                    <label class="text-sm px-3 py-1 rounded-full border cursor-pointer has-[:checked]:bg-brand-600 has-[:checked]:text-white has-[:checked]:border-brand-600">
                        <input type="checkbox" name="keahlian[]" value="{{ $k->id }}" class="hidden" @checked(in_array($k->id, $punya))>{{ $k->nama }}
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>

    @unless ($profil->isAktif())
        <div class="rounded-xl border bg-slate-50 p-3 text-sm">
            <p class="mb-2">Data profil dipakai untuk rekomendasi lowongan, proses lamaran, dan rekap statistik desa. Usaha tempat kamu melamar bisa melihat nomor HP mulai tahap wawancara. Baca <a href="{{ route('privasi') }}" class="link">kebijakan data Lokerin</a>.</p>
            <label class="flex items-start gap-2">
                <input type="checkbox" name="setuju" value="1" required class="mt-1" @checked(old('setuju'))>
                <span>Saya setuju data profil saya digunakan untuk keperluan tersebut.</span>
            </label>
        </div>
    @endunless
    <button class="btn btn-primary">Simpan profil</button>
</form>

@if ($profil->isAktif())
    <section class="card mt-4 max-w-2xl p-5 text-sm">
        <h2 class="font-semibold">Persetujuan data aktif</h2>
        <p class="mt-1 text-slate-600">Dicatat pada {{ $profil->persetujuan_data_at->format('d/m/Y') }}. Baca <a href="{{ route('privasi') }}" class="link">kebijakan data Lokerin</a>.</p>
        <p class="mt-2 text-slate-600">Menarik persetujuan menonaktifkan rekomendasi dan pengiriman lamaran baru. Riwayat lamaran tetap bisa kamu lihat.</p>
        <form method="POST" action="{{ route('pencari.persetujuan.tarik') }}" class="mt-3">
            @csrf
            @method('DELETE')
            <button class="btn btn-secondary px-3 py-2 font-medium text-red-700">Tarik persetujuan</button>
        </form>
    </section>
@endif
@endsection
