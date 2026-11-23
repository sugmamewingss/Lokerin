<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\Lowongan;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublikasiLowonganController extends Controller
{
    public function index(Request $request)
    {
        $desa = $request->user()->desa;
        abort_unless($desa, 403, 'Akun kader belum terhubung ke desa.');

        $desaSaja = $request->query('cakupan') === 'desa';
        $wilayah = $desaSaja ? "Desa {$desa->nama}" : "Kecamatan {$desa->kecamatan}";
        $waktu = now('Asia/Jakarta')->format('d/m/Y H:i').' WIB';
        $katalog = route('lowongan.index', $desaSaja ? ['desa' => $desa->id] : []);
        $lowongan = Lowongan::tampil()
            ->whereHas('usaha.desa', fn ($q) => $desaSaja ? $q->whereKey($desa->id) : $q->where('kecamatan', $desa->kecamatan))
            ->with(['usaha:id,nama_usaha,desa_id', 'usaha.desa:id,nama'])
            ->orderByRaw('tanggal_tutup IS NULL')->orderBy('tanggal_tutup')->orderBy('judul')->orderBy('id')
            ->get(['id', 'usaha_id', 'judul', 'jenis_kerja', 'pendidikan_min', 'tanggal_tutup']);
        $ringkasan = $this->ringkasan($lowongan, $desa->nama, $wilayah, $waktu, $katalog);

        return view('kader.publikasi-lowongan', compact('desa', 'desaSaja', 'wilayah', 'waktu', 'katalog', 'lowongan', 'ringkasan'));
    }

    private function ringkasan(Collection $lowongan, string $desa, string $wilayah, string $waktu, string $katalog): string
    {
        $batas = 10;
        $entri = $lowongan->take($batas)->map(function (Lowongan $item, int $index) {
            $judul = Str::squish($item->judul);
            $usaha = Str::squish($item->usaha->nama_usaha);
            $lokasi = $item->usaha->desa->nama;
            $jenis = Lowongan::JENIS_KERJA[$item->jenis_kerja];
            $tutup = $item->tanggal_tutup?->format('d/m/Y') ?? 'belum ditentukan';

            return implode("\n", [
                ($index + 1).". $judul — $usaha, $lokasi",
                "$jenis · Pendidikan min. {$item->pendidikan_min} · Batas melamar $tutup",
                route('lowongan.detail', $item),
            ]);
        })->all();

        $bagian = ["Lowongan aktif $wilayah\nDari kader Desa $desa · $waktu", ...$entri];
        if ($lowongan->count() > $batas) {
            $bagian[] = "Ringkasan $batas dari {$lowongan->count()} lowongan dalam cakupan ini.";
        }
        $bagian[] = "Cek status terbaru dan cara melamar di Lokerin atau minta bantuan kader desa.\n$katalog";

        return implode("\n\n", $bagian);
    }
}
