<?php

namespace App\Http\Controllers\Disnaker;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\LogAkses;
use App\Models\RekapCommandCenter;
use App\Services\RekapKetenagakerjaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CommandCenterController extends Controller
{
    private const INDIKATOR = ['pencari', 'lowongan', 'lamaran', 'penempatan'];

    public function index(RekapKetenagakerjaan $data): Response
    {
        $paket = $this->paket($data);
        $sidikData = $this->sidikData($paket);
        $riwayat = RekapCommandCenter::orderByDesc('id')->paginate(10);

        return response()->view('disnaker.command-center', compact('paket', 'sidikData', 'riwayat'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, RekapKetenagakerjaan $data): RedirectResponse
    {
        $input = $request->validate(['sidik_data' => ['required', 'string', 'size:64']]);
        $paket = $this->paket($data);
        $sidikData = $this->sidikData($paket);

        if (!$paket['per_desa'] || !hash_equals($sidikData, $input['sidik_data'])) {
            return to_route('disnaker.command-center.index')
                ->withErrors(['sidik_data' => 'Rekap berubah atau belum tersedia. Tinjau rekap terbaru sebelum menyetujui.']);
        }

        DB::transaction(function () use ($request, $paket, $sidikData) {
            $rekap = RekapCommandCenter::createOrFirst(['sidik_data' => $sidikData], [
                'data' => $paket,
                'disetujui_oleh' => $request->user()->id,
                'disetujui_pada' => now(),
            ]);
            if ($rekap->wasRecentlyCreated) {
                LogAkses::catat('setujui_rekap_command_center', 'rekap_command_center#'.$rekap->id);
            }
        }, 3);

        return to_route('disnaker.command-center.index')->with('sukses', 'Rekap yang disetujui tersedia untuk diunduh.');
    }

    public function download(RekapCommandCenter $rekap): JsonResponse
    {
        return response()->json([
            ...$rekap->data,
            'disetujui_pada' => $rekap->disetujui_pada->timezone('Asia/Jakarta')->toIso8601String(),
        ], 200, [
            'Cache-Control' => 'private, no-store',
            'Content-Disposition' => 'attachment; filename="rekap-pakis-'.$rekap->id.'.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function paket(RekapKetenagakerjaan $data): array
    {
        $perDesa = $data->perDesa(Desa::where('kecamatan', RekapKetenagakerjaan::KECAMATAN))
            ->map(fn (array $baris) => Arr::only($baris, ['desa', ...self::INDIKATOR]));
        $total = [];
        foreach (self::INDIKATOR as $indikator) {
            $total[$indikator] = $perDesa->sum($indikator);
        }

        return [
            'sumber' => 'Lokerin',
            'kecamatan' => RekapKetenagakerjaan::KECAMATAN,
            'tanggal_rekap_wib' => now('Asia/Jakarta')->toDateString(),
            'per_desa' => $perDesa->all(),
            'total' => $total,
        ];
    }

    private function sidikData(array $paket): string
    {
        return hash('sha256', json_encode($paket, JSON_THROW_ON_ERROR));
    }
}
