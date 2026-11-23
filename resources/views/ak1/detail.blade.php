@extends('layouts.app', ['judul' => 'Detail permohonan AK/1'])

@section('konten')
<div class="max-w-2xl space-y-4">
    <a href="{{ route('ak1.index') }}" class="inline-block text-sm font-medium text-blue-700 underline">Kembali ke permohonan</a>
    <h1 class="text-2xl font-bold">Permohonan AK/1 Pakiskembar</h1>
    @include('partials.pemberitahuan-ak1')

    <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 sm:p-5" aria-labelledby="status-ak1">
        <h2 id="status-ak1" class="font-semibold">{{ $permohonan->status->label() }}</h2>
        <p class="text-sm text-slate-600">Pemohon {{ $permohonan->pemohon->name }}</p>
        <p class="text-sm">{{ $permohonan->status->petunjuk() }}</p>
        <a href="{{ route('ak1.panduan') }}" class="inline-block text-sm font-medium text-blue-700 underline">Lihat panduan dan layanan resmi</a>
        @if ($langkah = $permohonan->status->langkahBerikutnya($kader))
            <form method="POST" action="{{ route('ak1.update', $permohonan) }}" class="flex flex-wrap gap-2 pt-1">
                @csrf
                @method('PATCH')
                @foreach ($langkah as $status)
                    <button name="status" value="{{ $status->value }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-slate-50">{{ $status->tindakan() }}</button>
                @endforeach
            </form>
        @endif
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5" aria-labelledby="riwayat-status-ak1">
        <h2 id="riwayat-status-ak1" class="mb-3 font-semibold">Riwayat status</h2>
        <ol class="space-y-3 border-l-2 border-blue-100 pl-4 text-sm">
            @foreach ($riwayat as $perubahan)
                <li>
                    <p class="font-medium">{{ \App\Enums\StatusAk1::from($perubahan->aksi)->label() }}</p>
                    <p class="text-slate-600">
                        {{ $perubahan->user_id === $permohonan->pemohon_id ? 'Warga' : 'Kader' }} ·
                        <time datetime="{{ $perubahan->waktu->toIso8601String() }}">{{ $perubahan->waktu->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</time>
                    </p>
                </li>
            @endforeach
        </ol>
    </section>
</div>
@endsection
