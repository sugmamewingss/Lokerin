@php $banjir = $desaLokasi?->informasiBanjir(); @endphp

<section class="my-5 space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm" aria-labelledby="informasi-banjir">
    <h2 id="informasi-banjir" class="font-semibold text-slate-900">Informasi banjir</h2>
    @if (!$desaLokasi)
        <p class="text-slate-600">Desa lokasi pelatihan belum dicantumkan.</p>
    @elseif (!$banjir)
        <p class="text-slate-600">Rujukan banjir untuk {{ $desaLokasi->nama }} belum tersedia di Lokerin.</p>
    @else
        <div class="space-y-1">
            <p>Banjir tercantum sebagai ancaman di Desa {{ $desaLokasi->nama }}, Kecamatan {{ $desaLokasi->kecamatan }}.</p>
            <p class="text-slate-600"><a href="{{ $banjir['acuan']['url'] }}" class="text-blue-700 underline">{{ $banjir['acuan']['sumber'] }}</a></p>
        </div>
        @if ($banjir['kejadian'])
            <div>
                <h3 class="font-medium">Kejadian terdokumentasi</h3>
                <ul class="mt-1 list-disc space-y-2 pl-5">
                    @foreach ($banjir['kejadian'] as $kejadian)
                        <li>
                            <time datetime="{{ $kejadian['tanggal'] }}" class="font-medium">{{ \Carbon\CarbonImmutable::parse($kejadian['tanggal'])->format('d/m/Y') }}</time>
                            · {{ $kejadian['uraian'] }}
                            <a href="{{ $kejadian['url'] }}" class="text-blue-700 underline">{{ $kejadian['sumber'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <p class="text-slate-600">Lokerin belum memuat laporan kejadian khusus desa ini.</p>
        @endif
        <p class="text-slate-600">Catatan ini mencakup tingkat desa. Kondisi lokasi kegiatan dan akses perjalanan dapat berbeda.</p>
    @endif
    <p><a href="https://inarisk.bnpb.go.id/webgis/" class="font-medium text-blue-700 underline">Lihat peta InaRISK BNPB</a></p>
</section>
