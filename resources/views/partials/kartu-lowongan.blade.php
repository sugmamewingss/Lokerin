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
        <span class="px-2 py-0.5 rounded bg-slate-100">{{ \App\Models\Lowongan::JENIS_KERJA[$l->jenis_kerja] }}</span>
        <span class="px-2 py-0.5 rounded bg-slate-100">min. {{ $l->pendidikan_min }}</span>
        @if ($gaji = $l->rentangGaji())
            <span class="px-2 py-0.5 rounded bg-slate-100">{{ $gaji }}</span>
        @endif
    </div>
    @isset($hasil)
        @include('partials.ringkasan-kecocokan', ['lowongan' => $l, 'hasil' => $hasil])
    @endisset
</a>
