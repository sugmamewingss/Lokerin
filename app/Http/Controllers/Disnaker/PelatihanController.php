<?php

namespace App\Http\Controllers\Disnaker;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Keahlian;
use App\Models\Pelatihan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelatihanController extends Controller
{
    public function index()
    {
        $pelatihan = Pelatihan::withTrashed()->with('keahlian')
            ->orderBy('deleted_at')->orderBy('judul')->orderBy('id')->paginate(15);

        return view('disnaker.pelatihan.index', compact('pelatihan'));
    }

    public function create()
    {
        return $this->form(new Pelatihan);
    }

    public function store(Request $request)
    {
        Pelatihan::create($this->validasi($request));

        return redirect()->route('disnaker.pelatihan.index')->with('sukses', 'Pelatihan dipublikasikan.');
    }

    public function edit(Pelatihan $pelatihan)
    {
        return $this->form($pelatihan);
    }

    public function update(Request $request, Pelatihan $pelatihan)
    {
        $pelatihan->update($this->validasi($request));

        return redirect()->route('disnaker.pelatihan.index')->with('sukses', 'Pelatihan diperbarui.');
    }

    public function destroy(Pelatihan $pelatihan)
    {
        $pelatihan->delete();

        return redirect()->route('disnaker.pelatihan.index')->with('sukses', 'Pelatihan diarsipkan.');
    }

    public function pulihkan(Pelatihan $pelatihan)
    {
        if ($pelatihan->trashed()) {
            $pelatihan->restore();
        }

        return redirect()->route('disnaker.pelatihan.index')->with('sukses', 'Pelatihan tersedia di katalog.');
    }

    private function form(Pelatihan $pelatihan)
    {
        $keahlian = Keahlian::orderBy('nama')->get(['id', 'nama']);
        $desa = Desa::orderBy('kecamatan')->orderBy('nama')->get(['id', 'nama', 'kecamatan']);

        return view('disnaker.pelatihan.form', compact('pelatihan', 'keahlian', 'desa'));
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'penyelenggara' => ['required', 'string', 'max:150'],
            'jenis_penyelenggara' => ['required', Rule::in(array_keys(Pelatihan::JENIS_PENYELENGGARA))],
            'keahlian_id' => ['required', 'integer', 'exists:keahlian,id'],
            'jadwal' => ['nullable', 'string', 'max:100'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'desa_id' => ['nullable', 'integer', 'exists:desa,id'],
            'kuota' => ['nullable', 'integer', 'between:1,65535'],
        ], [], ['jenis_penyelenggara' => 'jenis penyelenggara', 'keahlian_id' => 'keahlian']);
    }
}
