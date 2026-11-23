<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.masuk');
    }

    /** Login dengan email ATAU nomor HP (F-01). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'identitas' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $kolom = filter_var($data['identitas'], FILTER_VALIDATE_EMAIL) ? 'email' : 'no_hp';

        if (!Auth::attempt([$kolom => $data['identitas'], 'password' => $data['password'], 'menunggu_aktivasi' => false], $request->boolean('ingat'))) {
            throw ValidationException::withMessages(['identitas' => 'Email/nomor HP atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->homeRoute()));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda');
    }
}
