<section class="my-6 space-y-4" aria-labelledby="indikator-mangliawan">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 id="indikator-mangliawan" class="text-lg font-bold">Indikator uji coba Mangliawan</h2>
            @if ($ujiCoba['mulai'])
                <p class="text-sm text-slate-600">Periode {{ $ujiCoba['mulai']->format('d/m/Y') }}–{{ $ujiCoba['sampai']->format('d/m/Y') }} WIB</p>
            @else
                <p class="text-sm text-slate-600">Semua catatan tersedia. Pilih tanggal mulai untuk mengukur periode enam bulan.</p>
            @endif
        </div>
        <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-end gap-2 text-sm">
            <label class="block">Tanggal mulai uji coba
                <input type="date" name="mulai" value="{{ $ujiCoba['mulai']?->toDateString() }}" max="{{ now('Asia/Jakarta')->toDateString() }}" required class="mt-1 block rounded-lg border-slate-300 bg-white">
            </label>
            <button class="rounded-lg bg-blue-600 px-3 py-2 font-semibold text-white">Terapkan periode</button>
            @if ($ujiCoba['mulai'])
                <a href="{{ url()->current() }}" class="px-1 py-2 text-blue-700 underline">Semua catatan</a>
            @endif
        </form>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold">Pendaftar aktif</h3>
            <p class="mt-2 text-2xl font-bold">{{ number_format($ujiCoba['pendaftar'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm text-slate-600">Target sekitar 250 warga atau 50% estimasi penganggur</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold">UMKM pemasang lowongan</h3>
            <p class="mt-2 text-2xl font-bold">{{ number_format($ujiCoba['umkm'], 0, ',', '.') }}</p>
            <p class="mt-1 text-sm text-slate-600">Target minimal 30 UMKM</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <h3 class="text-sm font-semibold">Pendaftar yang ditempatkan</h3>
            @if ($ujiCoba['persen_penempatan'] !== null)
                <p class="mt-2 text-2xl font-bold">{{ number_format($ujiCoba['persen_penempatan'], 1, ',', '.') }}%</p>
                <p class="text-sm text-slate-600">{{ $ujiCoba['warga_ditempatkan'] }} dari {{ $ujiCoba['pendaftar'] }} pendaftar</p>
            @else
                <p class="mt-2 font-semibold">Belum ada pendaftar</p>
            @endif
            <p class="mt-1 text-sm text-slate-600">Target minimal 30% pendaftar</p>
        </article>
    </div>
    <p class="text-sm text-slate-600">Pendaftar dihitung dari tanggal persetujuan profil yang masih aktif. UMKM dihitung sekali jika terverifikasi dan memiliki lowongan dengan tanggal terbit tercatat. Tingkat penempatan menghitung warga unik dengan catatan penempatan dalam periode.</p>

    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm">
        <h3 class="mb-2 font-semibold">Indikator dari sumber eksternal</h3>
        <dl class="grid gap-3 sm:grid-cols-2">
            <div>
                <dt class="font-medium">TPT Kabupaten Malang</dt>
                <dd class="mt-1 text-slate-600">Baseline 5,00% pada 2025 dan target usulan 4,8% pada 2026. Sumber BPS/Sakernas.</dd>
            </div>
            <div>
                <dt class="font-medium">Kader digital desa terlatih</dt>
                <dd class="mt-1 text-slate-600">Target minimal dua per desa kajian. Realisasi bersumber dari laporan desa.</dd>
            </div>
        </dl>
    </div>
</section>
