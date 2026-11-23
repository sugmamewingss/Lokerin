@extends('layouts.app', ['judul' => 'Profil usaha'])

@section('konten')
<form method="POST" action="{{ route('usaha.profil.update') }}" class="max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-5">
    @csrf
    @method('PUT')

    <h1 class="text-xl font-bold">Profil usaha</h1>
    @if ($usaha->exists)
        <p class="text-sm text-slate-600">Perubahan data profil memerlukan verifikasi ulang. Lowonganmu disembunyikan sementara sampai profil disetujui lagi.</p>
    @endif

    <label class="block text-sm font-medium">Nama usaha
        <input name="nama_usaha" value="{{ old('nama_usaha', $usaha->nama_usaha) }}" maxlength="150" required class="mt-1 w-full rounded-lg border-slate-300">
    </label>
    <label class="block text-sm font-medium">Jenis usaha
        <input name="jenis_usaha" value="{{ old('jenis_usaha', $usaha->jenis_usaha) }}" maxlength="100" required class="mt-1 w-full rounded-lg border-slate-300">
    </label>
    @php $skala = $usaha->is_umkm === null ? '' : (string) (int) $usaha->is_umkm; @endphp
    <label class="block text-sm font-medium">Skala usaha
        <select name="is_umkm" class="mt-1 w-full rounded-lg border-slate-300">
            <option value="">Belum ditentukan</option>
            <option value="1" @selected((string) old('is_umkm', $skala) === '1')>UMKM</option>
            <option value="0" @selected((string) old('is_umkm', $skala) === '0')>Perusahaan non-UMKM</option>
        </select>
    </label>
    <label class="block text-sm font-medium">Desa tempat usaha
        <select name="desa_id" required class="mt-1 w-full rounded-lg border-slate-300">
            <option value="">Pilih desa</option>
            @foreach ($desa as $d)
                <option value="{{ $d->id }}" @selected(old('desa_id', $usaha->desa_id) == $d->id)>{{ $d->nama }}</option>
            @endforeach
        </select>
    </label>
    <label class="block text-sm font-medium">Alamat usaha untuk verifikasi
        <textarea name="alamat" rows="2" maxlength="255" required class="mt-1 w-full rounded-lg border-slate-300">{{ old('alamat', $usaha->alamat) }}</textarea>
    </label>
    <div class="flex flex-wrap items-center gap-4">
        <button class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">Simpan profil</button>
        <a href="{{ route('usaha.beranda') }}" class="text-sm text-blue-700 underline">Kembali ke usaha saya</a>
    </div>
</form>
@endsection
