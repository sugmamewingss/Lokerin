@extends('layouts.app', ['judul' => 'Profil'])
@section('konten')
<form method="POST" action="{{ route('pencari.profil') }}" class="bg-white rounded-xl border border-slate-200 p-5 space-y-4 max-w-2xl">
    @csrf @method('PUT')
    <h1 class="text-xl font-bold">Profil dan keahlian</h1>
    <div class="grid sm:grid-cols-2 gap-3 text-sm">
        <label>Desa
            <select name="desa_id" class="mt-1 w-full rounded-lg border-slate-300">
                @foreach ($desa as $d)<option value="{{ $d->id }}" @selected(old('desa_id', $user->desa_id) == $d->id)>{{ $d->nama }}</option>@endforeach
            </select>
        </label>
        <label>Tanggal lahir <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $profil->tanggal_lahir?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border-slate-300"></label>
        <label>Jenis kelamin
            <select name="jenis_kelamin" class="mt-1 w-full rounded-lg border-slate-300">
                <option value="">-</option>
                <option value="L" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin', $profil->jenis_kelamin) === 'P')>Perempuan</option>
            </select>
        </label>
        <label>Pendidikan terakhir
            <select name="pendidikan_terakhir" class="mt-1 w-full rounded-lg border-slate-300">
                @foreach (array_keys(config('lokerin.level_pendidikan')) as $p)<option @selected(old('pendidikan_terakhir', $profil->pendidikan_terakhir) === $p)>{{ $p }}</option>@endforeach
            </select>
        </label>
        <label>Jurusan
            <input list="jurusan-list" name="jurusan" value="{{ old('jurusan', $profil->jurusan) }}" class="mt-1 w-full rounded-lg border-slate-300">
            <datalist id="jurusan-list">@foreach ($jurusanList as $j)<option value="{{ $j }}">@endforeach</datalist>
        </label>
        <label>Status kerja
            <select name="status_kerja" class="mt-1 w-full rounded-lg border-slate-300">
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
                    <label class="text-sm px-3 py-1 rounded-full border cursor-pointer has-[:checked]:bg-blue-600 has-[:checked]:text-white has-[:checked]:border-blue-600">
                        <input type="checkbox" name="keahlian[]" value="{{ $k->id }}" class="hidden" @checked(in_array($k->id, $punya))>{{ $k->nama }}
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>

    <label class="flex items-start gap-2 text-sm p-3 rounded-lg bg-slate-50 border">
        <input type="checkbox" name="setuju" value="1" class="mt-1" @checked($profil->isAktif())>
        <span>Saya setuju data profil ini dipakai untuk mencocokkan lowongan dan rekap statistik desa. Nomor HP saya hanya dibagikan ke usaha yang mengundang saya wawancara. Lokerin tidak meminta NIK atau foto KTP.</span>
    </label>
    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold">Simpan profil</button>
</form>
@endsection
