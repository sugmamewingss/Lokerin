@extends('layouts.app', ['judul' => 'Kelola keahlian'])

@section('konten')
<div class="mb-4 flex flex-wrap items-start justify-between gap-3">
    <h1 class="judul-halaman">Kelola keahlian</h1>
    <a href="{{ route('disnaker.pelatihan.index') }}" class="btn btn-secondary px-3 py-2">Kembali ke pelatihan</a>
</div>
<form method="POST" action="{{ route('disnaker.keahlian.store') }}" class="card mb-5 space-y-3 p-4">
    @csrf
    <input type="hidden" name="form_keahlian" value="baru">
    <h2 class="font-semibold">Tambah keahlian</h2>
    <div class="grid gap-3 text-sm sm:grid-cols-2">
        <label>Nama keahlian
            <input name="nama" value="{{ old('form_keahlian') === 'baru' ? old('nama') : '' }}" required maxlength="100" class="mt-1 w-full">
        </label>
        <label>Kategori (opsional)
            <input name="kategori" value="{{ old('form_keahlian') === 'baru' ? old('kategori') : '' }}" maxlength="50" class="mt-1 w-full">
        </label>
    </div>
    <button class="btn btn-primary">Tambah keahlian</button>
</form>

<div class="card divide-y">
    @foreach ($daftar as $item)
        @php $formAktif = old('form_keahlian') == $item->id; @endphp
        <details class="p-4" @if ($formAktif) open @endif>
            <summary class="cursor-pointer text-sm font-medium">{{ $item->nama }} @if ($item->kategori) · {{ $item->kategori }} @endif</summary>
            <form method="POST" action="{{ route('disnaker.keahlian.update', $item) }}" class="mt-3 space-y-3">
                @csrf @method('PUT')
                <input type="hidden" name="form_keahlian" value="{{ $item->id }}">
                <div class="grid gap-3 text-sm sm:grid-cols-2">
                    <label>Nama keahlian
                        <input name="nama" value="{{ $formAktif ? old('nama') : $item->nama }}" required maxlength="100" class="mt-1 w-full rounded-lg border-slate-300">
                    </label>
                    <label>Kategori (opsional)
                        <input name="kategori" value="{{ $formAktif ? old('kategori') : $item->kategori }}" maxlength="50" class="mt-1 w-full rounded-lg border-slate-300">
                    </label>
                </div>
                <button class="btn btn-secondary px-3 py-2">Simpan perubahan</button>
            </form>
        </details>
    @endforeach
</div>
<div class="mt-4">{{ $daftar->links() }}</div>
@endsection
