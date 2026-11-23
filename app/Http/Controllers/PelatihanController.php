<?php

namespace App\Http\Controllers;

use App\Models\Keahlian;
use App\Models\Pelatihan;
use Illuminate\Http\Request;

class PelatihanController extends Controller
{
    public function index(Request $request)
    {
        $keahlianId = $request->integer('keahlian');
        $pelatihan = Pelatihan::with('keahlian')
            ->when($keahlianId > 0, fn ($q) => $q->where('keahlian_id', $keahlianId))
            ->orderBy('judul')->orderBy('id')->paginate(12)->withQueryString();
        $keahlian = Keahlian::has('pelatihan')->orderBy('nama')->get(['id', 'nama']);

        return view('publik.pelatihan.index', compact('pelatihan', 'keahlian', 'keahlianId'));
    }

    public function show(Pelatihan $pelatihan)
    {
        $pelatihan->load(['keahlian', 'desa']);
        $lowongan = $pelatihan->keahlian->lowonganWajib()->tampil()
            ->with('usaha.desa')->latest('lowongan.created_at')->orderBy('lowongan.id')->paginate(12);

        return view('publik.pelatihan.detail', compact('pelatihan', 'lowongan'));
    }
}
