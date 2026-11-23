@php
    $kolom = ['pencari' => 'Pencari kerja', 'lowongan' => 'Lowongan aktif', 'lamaran' => 'Lamaran', 'penempatan' => 'Penempatan'];
@endphp

<div class="card overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100">
            <tr>
                <th scope="col" class="p-2 text-left">Desa</th>
                @foreach ($kolom as $label)
                    <th scope="col" class="p-2 text-right">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rekap as $baris)
                <tr class="border-t">
                    <th scope="row" class="p-2 text-left font-medium">{{ $baris['desa'] }}</th>
                    @foreach ($kolom as $indikator => $label)
                        <td class="p-2 text-right tabular-nums">{{ $baris[$indikator] }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="5" class="p-3 text-slate-600">Belum ada data desa di Kecamatan Pakis.</td></tr>
            @endforelse
        </tbody>
        <tfoot class="border-t font-semibold">
            <tr>
                <th scope="row" class="p-2 text-left">Total</th>
                @foreach ($kolom as $indikator => $label)
                    <td class="p-2 text-right">{{ $rekap->sum($indikator) }}</td>
                @endforeach
            </tr>
        </tfoot>
    </table>
</div>
