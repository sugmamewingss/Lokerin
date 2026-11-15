@extends('layouts.app', ['judul' => 'Dasbor kecamatan'])
@section('konten')
<h1 class="text-xl font-bold mb-4">Dasbor Kecamatan Pakis</h1>
<div class="overflow-x-auto">
<table class="w-full bg-white rounded-xl border text-sm">
    <thead class="bg-slate-100"><tr>
        <th class="p-2 text-left">Desa</th><th class="p-2 text-right">Pencari kerja</th><th class="p-2 text-right">Lowongan aktif</th><th class="p-2 text-right">Lamaran</th><th class="p-2 text-right">Penempatan</th>
    </tr></thead>
    <tbody>
    @foreach ($rekap as $r)
        <tr class="border-t"><td class="p-2">{{ $r['desa'] }}</td><td class="p-2 text-right">{{ $r['pencari'] }}</td><td class="p-2 text-right">{{ $r['lowongan'] }}</td><td class="p-2 text-right">{{ $r['lamaran'] }}</td><td class="p-2 text-right">{{ $r['penempatan'] }}</td></tr>
    @endforeach
    </tbody>
    <tfoot class="font-semibold border-t"><tr>
        <td class="p-2">Total</td>
        @foreach (['pencari', 'lowongan', 'lamaran', 'penempatan'] as $k)<td class="p-2 text-right">{{ $rekap->sum($k) }}</td>@endforeach
    </tr></tfoot>
</table>
</div>
<p class="mt-2 text-xs text-slate-400">TODO (Muflih): grafik dan ekspor CSV.</p>
@endsection
