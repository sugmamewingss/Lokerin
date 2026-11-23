@extends('layouts.app', ['judul' => 'Pendampingan AK/1'])

@section('konten')
<div class="max-w-2xl space-y-4">
    <header class="space-y-1">
        <h1 class="text-2xl font-bold">Pendampingan AK/1 Pakiskembar</h1>
        <a href="{{ route('ak1.panduan') }}" class="text-sm font-medium text-blue-700 underline">Lihat panduan dan layanan resmi</a>
    </header>

    @include('partials.pemberitahuan-ak1')

    @if ($bisaMengajukan)
        <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 sm:p-5" aria-labelledby="ajukan-ak1">
            <h2 id="ajukan-ak1" class="font-semibold">{{ $kader ? 'Ajukan untuk warga' : 'Ajukan pendampingan' }}</h2>
            <form method="POST" action="{{ route('ak1.store') }}" class="space-y-4 text-sm">
                @csrf
                @if ($kader)
                    <div>
                        <label for="nomor-warga-ak1" class="block font-medium">Nomor HP akun warga</label>
                        <input id="nomor-warga-ak1" type="tel" name="nomor_warga_ak1" required maxlength="20" autocomplete="off" aria-describedby="petunjuk-nomor" class="mt-1 w-full max-w-xs rounded-lg border-slate-300">
                        <p id="petunjuk-nomor" class="mt-1 text-slate-600">Gunakan nomor terdaftar milik warga Pakiskembar yang sudah mengaktifkan akun. Jika perlu, <a href="{{ route('kader.warga.index') }}" class="text-blue-700 underline">dampingi pendaftaran warga</a> terlebih dahulu.</p>
                    </div>
                @endif
                <label for="setuju-ak1" class="flex items-start gap-2">
                    <input id="setuju-ak1" name="setuju_ak1" value="1" type="checkbox" required class="mt-0.5 rounded border-slate-300 text-blue-600">
                    <span>{{ $kader ? 'Warga setuju saya mencatat pengajuan atas namanya dan mengetahui bahwa kader Pakiskembar dapat melihat nama serta riwayat permohonan.' : 'Saya setuju kader Pakiskembar dapat melihat nama saya dan riwayat permohonan untuk mendampingi pengurusan AK/1.' }}</span>
                </label>
                <button class="rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white">Catat permohonan</button>
            </form>
        </section>
    @else
        <p class="text-sm text-slate-600">Pengajuan pada simulasi ini tersedia untuk akun warga dengan desa Pakiskembar.</p>
    @endif

    <section class="space-y-3" aria-labelledby="riwayat-ak1">
        <h2 id="riwayat-ak1" class="font-semibold">{{ $kader ? 'Permohonan warga' : 'Permohonan saya' }}</h2>
        <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200 bg-white">
            @forelse ($permohonan as $item)
                <li>
                    <a href="{{ route('ak1.show', $item) }}" class="flex flex-wrap items-center justify-between gap-2 p-4 text-sm hover:bg-slate-50">
                        <span class="font-medium text-blue-700 underline">
                            @if ($kader) {{ $item->pemohon->name }} · @endif
                            {{ $item->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB
                        </span>
                        <span class="rounded-full bg-slate-100 px-2 py-1">{{ $item->status->label() }}</span>
                    </a>
                </li>
            @empty
                <li class="p-4 text-sm text-slate-600">Belum ada permohonan.</li>
            @endforelse
        </ul>
        {{ $permohonan->links() }}
    </section>
</div>
@endsection
