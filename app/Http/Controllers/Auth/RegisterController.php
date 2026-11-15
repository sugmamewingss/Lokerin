<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.daftar', ['desa' => Desa::orderBy('nama')->get()]);
    }

    /**
     * Pendaftaran mandiri hanya untuk pencari kerja dan usaha.
     * Akun kader dan Disnaker dibuat oleh admin (seeder).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'max:20', 'unique:users,no_hp'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in([User::ROLE_PENCARI, User::ROLE_USAHA])],
            'desa_id' => ['required', 'exists:desa,id'],
        ]);

        // Kolom email bawaan Laravel wajib unik dan tidak boleh kosong, jadi dibuat placeholder dari no HP.
        $data['email'] ??= preg_replace('/\D/', '', $data['no_hp']).'@hp.lokerin.local';

        $user = User::create($data);

        if ($user->role === User::ROLE_PENCARI) {
            $user->profilPencari()->create();
        }

        Auth::login($user);

        return $user->role === User::ROLE_PENCARI
            ? redirect()->route('pencari.profil')->with('info', 'Lengkapi profilmu agar Lokerin bisa merekomendasikan lowongan.')
            : redirect()->route('usaha.beranda')->with('info', 'Lengkapi profil usaha. Lowongan tampil setelah diverifikasi kader desa.');
    }
}
