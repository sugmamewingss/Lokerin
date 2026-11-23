@extends('layouts.app', ['judul' => $lowongan->exists ? 'Kelola lowongan' : 'Buat lowongan'])

@section('konten')
<form method="POST" action="{{ $lowongan->exists ? route('usaha.lowongan.update', $lowongan) : route('usaha.lowongan.store') }}" class="max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-5">
    @csrf
    @if ($lowongan->exists)
        @method('PUT')
    @endif

    @php $kelasInput = 'mt-1 w-full rounded-lg border-slate-300'; @endphp
    <h1 class="text-xl font-bold">{{ $lowongan->exists ? 'Kelola lowongan' : 'Buat lowongan' }}</h1>

    <label class="block text-sm font-medium">Judul lowongan
        <input name="judul" required maxlength="150" value="{{ old('judul', $lowongan->judul) }}" class="{{ $kelasInput }}">
    </label>
    <label class="block text-sm font-medium">Deskripsi pekerjaan
        <textarea name="deskripsi" rows="4" maxlength="10000" class="{{ $kelasInput }}">{{ old('deskripsi', $lowongan->deskripsi) }}</textarea>
        <span class="mt-1 block font-normal text-slate-600">Wajib untuk lowongan aktif dan dapat dilihat publik.</span>
    </label>

    <div class="grid gap-4 text-sm sm:grid-cols-2">
        <label>Jenis kerja
            <select name="jenis_kerja" required class="{{ $kelasInput }}">
                @foreach (\App\Models\Lowongan::JENIS_KERJA as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(old('jenis_kerja', $lowongan->jenis_kerja) === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label>Kuota pelamar yang akan diterima
            <input type="number" name="kuota" min="1" max="65535" required value="{{ old('kuota', $lowongan->kuota) }}" class="{{ $kelasInput }}">
        </label>
        <label>Pendidikan minimal
            <select name="pendidikan_min" required class="{{ $kelasInput }}">
                <option value="">Pilih pendidikan</option>
                @foreach (array_keys(config('lokerin.level_pendidikan')) as $tingkat)
                    <option value="{{ $tingkat }}" @selected(old('pendidikan_min', $lowongan->pendidikan_min) === $tingkat)>{{ $tingkat }}</option>
                @endforeach
            </select>
        </label>
        <label>Jurusan yang dicari (opsional)
            <input name="jurusan_target" list="jurusan-lowongan" maxlength="100" value="{{ old('jurusan_target', $lowongan->jurusan_target) }}" class="{{ $kelasInput }}">
            <datalist id="jurusan-lowongan">
                @foreach ($jurusan as $nama)<option value="{{ $nama }}"></option>@endforeach
            </datalist>
        </label>
        <label>Gaji minimum dalam rupiah (opsional)
            <input type="number" name="gaji_min" min="0" max="4294967295" value="{{ old('gaji_min', $lowongan->gaji_min) }}" class="{{ $kelasInput }}">
        </label>
        <label>Gaji maksimum dalam rupiah (opsional)
            <input type="number" name="gaji_max" min="0" max="4294967295" value="{{ old('gaji_max', $lowongan->gaji_max) }}" class="{{ $kelasInput }}">
        </label>
        <label>Batas tanggal melamar (opsional)
            <input type="date" name="tanggal_tutup" value="{{ old('tanggal_tutup', $lowongan->tanggal_tutup?->format('Y-m-d')) }}" class="{{ $kelasInput }}">
        </label>
        <label>Status lowongan
            <select name="status" required class="{{ $kelasInput }}">
                @foreach (\App\Models\Lowongan::STATUS as $nilai => $label)
                    @continue(!$bolehTerbit && $nilai === 'aktif')
                    <option value="{{ $nilai }}" @selected(old('status', $lowongan->status) === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>
    @if (!$bolehTerbit)
        <p class="text-sm text-slate-600">Status aktif tersedia setelah profil usaha diverifikasi oleh kader desa.</p>
    @endif

    <fieldset class="space-y-3">
        <legend class="font-semibold">Keahlian</legend>
        @php $pilihan = old('keahlian', $lowongan->keahlian->mapWithKeys(fn ($k) => [$k->id => $k->pivot->wajib ? 'wajib' : 'tambahan'])->all()); @endphp
        @foreach ($keahlian as $kategori => $daftar)
            <div>
                <p class="mb-1 text-sm font-medium text-slate-700">{{ $kategori }}</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($daftar as $k)
                        <label class="flex items-center justify-between gap-2 rounded-lg border border-slate-200 px-2 py-1 text-sm">
                            <span>{{ $k->nama }}</span>
                            <select name="keahlian[{{ $k->id }}]" class="rounded-lg border-slate-300 text-sm">
                                <option value="">Tidak dipilih</option>
                                <option value="wajib" @selected(($pilihan[$k->id] ?? null) === 'wajib')>Wajib</option>
                                <option value="tambahan" @selected(($pilihan[$k->id] ?? null) === 'tambahan')>Tambahan</option>
                            </select>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </fieldset>
    <div class="flex flex-wrap items-center gap-4">
        <button class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">Simpan lowongan</button>
        <a href="{{ route('usaha.beranda') }}" class="text-sm text-blue-700 underline">Kembali ke usaha saya</a>
    </div>
</form>
@endsection
