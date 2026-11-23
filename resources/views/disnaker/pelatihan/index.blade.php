@extends('layouts.app', ['judul' => 'Kelola pelatihan'])

@section('konten')
<div class="mb-4 flex flex-wrap items-start justify-between gap-3">
    <h1 class="text-xl font-bold">Kelola pelatihan</h1>
    <div class="flex flex-wrap gap-2 text-sm font-semibold">
        <a href="{{ route('disnaker.keahlian.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2">Kelola keahlian</a>
        <a href="{{ route('disnaker.pelatihan.create') }}" class="rounded-lg bg-blue-600 px-3 py-2 text-white">Tambah pelatihan</a>
    </div>
</div>

<div class="divide-y rounded-xl border border-slate-200 bg-white">
    @forelse ($pelatihan as $item)
        <div class="flex flex-wrap items-start justify-between gap-3 p-4">
            <div>
                <h2 class="font-semibold">
                    @if ($item->trashed())
                        {{ $item->judul }}
                    @else
                        <a href="{{ route('pelatihan.show', $item) }}" class="text-blue-700 underline">{{ $item->judul }}</a>
                    @endif
                </h2>
                <p class="text-sm text-slate-600">{{ $item->penyelenggara }} · {{ $item->keahlian->nama }}</p>
                @if ($item->trashed())
                    <p class="mt-1 text-sm text-slate-600">Diarsipkan</p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2 text-sm font-medium">
                @if ($item->trashed())
                    <form method="POST" action="{{ route('disnaker.pelatihan.pulihkan', $item) }}">
                        @csrf @method('PATCH')
                        <button class="rounded-lg border border-slate-300 px-3 py-2 text-blue-700" aria-label="Pulihkan {{ $item->judul }}">Pulihkan</button>
                    </form>
                @else
                    <a href="{{ route('disnaker.pelatihan.edit', $item) }}" class="rounded-lg border border-slate-300 px-3 py-2" aria-label="Ubah {{ $item->judul }}">Ubah</a>
                    <form method="POST" action="{{ route('disnaker.pelatihan.destroy', $item) }}">
                        @csrf @method('DELETE')
                        <button class="rounded-lg border border-slate-300 px-3 py-2 text-red-700" aria-label="Arsipkan {{ $item->judul }}">Arsipkan</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p class="p-4 text-sm text-slate-600">Belum ada pelatihan.</p>
    @endforelse
</div>
<div class="mt-4">{{ $pelatihan->links() }}</div>
@endsection
