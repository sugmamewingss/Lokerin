{{-- Pakai: @include('partials.kartu-lowongan', ['l' => $lowongan, 'hasil' => $hasil ?? null]) --}}
<a href="{{ route('lowongan.detail', $l) }}" class="block bg-white rounded-xl border border-slate-200 p-4 hover:border-blue-400">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="font-semibold">{{ $l->judul }}</h3>
            <p class="text-sm text-slate-500">{{ $l->usaha->nama_usaha }} · {{ $l->usaha->desa->nama }}</p>
        </div>
        @isset($hasil)
            <span class="shrink-0 text-sm font-semibold px-2 py-1 rounded-lg {{ $hasil['skor'] >= 75 ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-800' }}">{{ $hasil['skor'] }}% cocok</span>
        @endisset
    </div>
    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-600">
        <span class="px-2 py-0.5 rounded bg-slate-100">{{ str_replace('_', ' ', $l->jenis_kerja) }}</span>
        <span class="px-2 py-0.5 rounded bg-slate-100">min. {{ $l->pendidikan_min }}</span>
        @if ($l->gaji_min)
            <span class="px-2 py-0.5 rounded bg-slate-100">Rp{{ number_format($l->gaji_min, 0, ',', '.') }}@if($l->gaji_max) - {{ number_format($l->gaji_max, 0, ',', '.') }}@endif</span>
        @endif
    </div>
    @isset($hasil)
        <p class="mt-2 text-xs text-slate-500">
            {{ $hasil['J'] == 1 ? 'Jurusan sesuai' : ($hasil['J'] > 0 ? 'Jurusan serumpun' : 'Jurusan berbeda') }} ·
            {{ $hasil['D'] == 1 ? 'di desamu' : ($hasil['D'] >= 0.6 ? 'masih di Pakis' : 'di luar Pakis') }} ·
            keahlian {{ (int) round($hasil['K'] * 100) }}% terpenuhi
        </p>
    @endisset
</a>
