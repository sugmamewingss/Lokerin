@extends('layouts.app', ['judul' => 'Daftarkan warga'])

@section('konten')
<div class="max-w-xl space-y-5">
    <form method="POST" action="{{ route('kader.warga.store') }}" class="card space-y-4 p-5">
        @csrf
        <div>
            <h1 class="judul-halaman">Daftarkan warga</h1>
            <p class="text-sm text-slate-600">{{ $desa->nama }} · {{ $desa->kecamatan }}</p>
        </div>
        <p class="text-sm text-slate-600">Isi nama dan nomor HP saat mendampingi warga. Setelah menyimpan, serahkan tautan agar warga bisa membuat kata sandi di perangkatnya.</p>
        <label class="block text-sm font-medium">Nama lengkap warga
            <input name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="off" class="mt-1 block w-full">
        </label>
        <label class="block text-sm font-medium">Nomor HP warga
            <input type="tel" name="no_hp" value="{{ old('no_hp') }}" required maxlength="13" pattern="08[0-9]{8,11}" autocomplete="off" aria-describedby="format-hp" class="mt-1 block w-full">
        </label>
        <p id="format-hp" class="text-sm text-slate-600">Gunakan nomor berawalan 08 dengan 10–13 angka tanpa spasi. Warga akan memakai nomor ini untuk masuk.</p>
        <button class="btn btn-primary w-full sm:w-auto">Buat tautan aktivasi</button>
    </form>

    <section aria-labelledby="menunggu-aktivasi" class="card p-5">
        <h2 id="menunggu-aktivasi" class="font-semibold">Menunggu aktivasi</h2>
        <div class="mt-2 divide-y divide-slate-200">
            @forelse ($warga as $item)
                <div class="flex flex-wrap items-center justify-between gap-2 py-3">
                    <div>
                        <p class="text-sm font-medium">{{ $item->name }}</p>
                        <p class="text-sm text-slate-600">Nomor berakhiran {{ substr($item->no_hp, -4) }}</p>
                    </div>
                    <form action="{{ route('kader.warga.tautan', $item) }}" method="POST">
                        @csrf
                        <button class="btn btn-secondary px-3 py-2 font-medium" aria-label="Buat tautan baru untuk {{ $item->name }}">Buat tautan baru</button>
                    </form>
                </div>
            @empty
                <p class="py-2 text-sm text-slate-600">Belum ada warga yang menunggu aktivasi.</p>
            @endforelse
        </div>
        {{ $warga->links() }}
    </section>
</div>
@endsection
