@php $judulHalaman = $pelatihan->exists ? 'Ubah pelatihan' : 'Tambah pelatihan'; @endphp
@extends('layouts.app', ['judul' => $judulHalaman])

@section('konten')
<form method="POST" action="{{ $pelatihan->exists ? route('disnaker.pelatihan.update', $pelatihan) : route('disnaker.pelatihan.store') }}" class="max-w-2xl space-y-4 rounded-xl border border-slate-200 bg-white p-5">
    @csrf
    @if ($pelatihan->exists)
        @method('PUT')
    @endif

    <h1 class="judul-halaman">{{ $judulHalaman }}</h1>
    <label class="block text-sm font-medium">Nama pelatihan
        <input name="judul" value="{{ old('judul', $pelatihan->judul) }}" required maxlength="150" class="mt-1 w-full rounded-lg border-slate-300">
    </label>
    <div class="grid gap-4 text-sm sm:grid-cols-2">
        <label>Nama penyelenggara
            <input name="penyelenggara" value="{{ old('penyelenggara', $pelatihan->penyelenggara) }}" required maxlength="150" class="mt-1 w-full rounded-lg border-slate-300">
        </label>
        <label>Jenis penyelenggara
            <select name="jenis_penyelenggara" required class="mt-1 w-full">
                <option value="">Pilih jenis</option>
                @foreach (\App\Models\Pelatihan::JENIS_PENYELENGGARA as $jenis => $label)
                    <option value="{{ $jenis }}" @selected(old('jenis_penyelenggara', $pelatihan->jenis_penyelenggara) === $jenis)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <div>
            <label>Keahlian yang dipelajari
                <select name="keahlian_id" required class="mt-1 w-full">
                    <option value="">Pilih keahlian</option>
                    @foreach ($keahlian as $item)
                        <option value="{{ $item->id }}" @selected(old('keahlian_id', $pelatihan->keahlian_id) == $item->id)>{{ $item->nama }}</option>
                    @endforeach
                </select>
            </label>
            <a href="{{ route('disnaker.keahlian.index') }}" class="mt-1 inline-block link">Kelola daftar keahlian</a>
        </div>
        <label>Jadwal (opsional)
            <input name="jadwal" value="{{ old('jadwal', $pelatihan->jadwal) }}" maxlength="100" class="mt-1 w-full rounded-lg border-slate-300">
        </label>
        <label>Lokasi (opsional)
            <input name="lokasi" value="{{ old('lokasi', $pelatihan->lokasi) }}" maxlength="150" class="mt-1 w-full rounded-lg border-slate-300">
        </label>
        <label>Desa lokasi pelatihan (opsional)
            <select name="desa_id" aria-describedby="petunjuk-desa" class="mt-1 w-full">
                <option value="">Desa belum dicantumkan</option>
                @foreach ($desa as $item)
                    <option value="{{ $item->id }}" @selected(old('desa_id', $pelatihan->desa_id) == $item->id)>{{ $item->nama }} · {{ $item->kecamatan }}</option>
                @endforeach
            </select>
            <span id="petunjuk-desa" class="mt-1 block text-slate-600">Pilih desa kegiatan agar informasi banjir sesuai lokasi. Pelatihan daring boleh dikosongkan.</span>
        </label>
        <label>Kuota peserta (opsional)
            <input type="number" name="kuota" value="{{ old('kuota', $pelatihan->kuota) }}" min="1" max="65535" class="mt-1 w-full rounded-lg border-slate-300">
        </label>
    </div>
    <div class="flex flex-wrap items-center gap-3 text-sm font-semibold">
        <button class="btn btn-primary">{{ $pelatihan->exists ? 'Simpan perubahan' : 'Publikasikan pelatihan' }}</button>
        <a href="{{ route('disnaker.pelatihan.index') }}" class="link">Kembali ke pengelolaan</a>
    </div>
</form>
@endsection
