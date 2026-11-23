<?php

namespace App\Http\Controllers\Disnaker;

use App\Http\Controllers\Controller;
use App\Models\Keahlian;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KeahlianController extends Controller
{
    public function index()
    {
        return view('disnaker.keahlian', ['daftar' => Keahlian::orderBy('nama')->paginate(15)]);
    }

    public function store(Request $request)
    {
        Keahlian::create($this->validasi($request));

        return redirect()->route('disnaker.keahlian.index')->with('sukses', 'Keahlian ditambahkan.');
    }

    public function update(Request $request, Keahlian $keahlian)
    {
        $keahlian->update($this->validasi($request, $keahlian));

        return back()->with('sukses', 'Keahlian diperbarui.');
    }

    private function validasi(Request $request, ?Keahlian $keahlian = null): array
    {
        $unik = Rule::unique('keahlian', 'nama');
        if ($keahlian) {
            $unik->ignore($keahlian);
        }

        return $request->validate([
            'nama' => ['required', 'string', 'max:100', $unik],
            'kategori' => ['nullable', 'string', 'max:50'],
        ], ['nama.unique' => 'Nama keahlian sudah ada.']);
    }
}
