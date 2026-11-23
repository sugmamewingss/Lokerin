@extends('layouts.app', ['judul' => 'Rekap Command Center'])

@section('konten')
<div class="space-y-6">
    <header class="space-y-2">
        <a href="{{ route('disnaker.dasbor') }}" class="inline-block text-sm link">Kembali ke dasbor</a>
        <h1 class="judul-halaman">Rekap Command Center</h1>
        <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
            Persetujuan dan unduhan tersedia di Lokerin. Pengiriman ke Command Center menunggu spesifikasi dan akses uji dari Diskominfo.
        </p>
    </header>

    <section aria-labelledby="pratinjau-rekap" class="space-y-3">
        <h2 id="pratinjau-rekap" class="text-lg font-semibold">Tinjau rekap Kecamatan Pakis</h2>
        <p class="text-sm text-slate-600">Tanggal rekap {{ $paket['tanggal_rekap_wib'] }} WIB. Persetujuan menyimpan salinan angka yang ditampilkan.</p>
        @include('disnaker.partials.tabel-rekap-desa', ['rekap' => collect($paket['per_desa'])])
        @if ($paket['per_desa'])
            <form method="POST" action="{{ route('disnaker.command-center.store') }}">
                @csrf
                <input type="hidden" name="sidik_data" value="{{ $sidikData }}">
                <button class="btn btn-primary">Setujui rekap</button>
            </form>
        @endif
    </section>

    <section aria-labelledby="riwayat-rekap" class="space-y-3">
        <h2 id="riwayat-rekap" class="text-lg font-semibold">Rekap yang disetujui</h2>
        <ul class="card divide-y divide-slate-200">
            @forelse ($riwayat as $rekap)
                <li class="flex flex-wrap items-center justify-between gap-2 p-4 text-sm">
                    <div>
                        <p class="font-semibold">Rekap {{ $rekap->data['tanggal_rekap_wib'] }}</p>
                        <p class="text-slate-600">Disetujui {{ $rekap->disetujui_pada->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</p>
                    </div>
                    <a href="{{ route('disnaker.command-center.download', $rekap) }}" class="font-semibold link">Unduh JSON<span class="sr-only"> rekap {{ $rekap->id }}</span></a>
                </li>
            @empty
                <li class="p-4 text-sm text-slate-600">Belum ada rekap yang disetujui.</li>
            @endforelse
        </ul>
        {{ $riwayat->links() }}
    </section>
</div>
@endsection
