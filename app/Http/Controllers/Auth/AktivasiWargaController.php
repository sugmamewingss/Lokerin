<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as AturanKataSandi;
use Illuminate\Validation\ValidationException;

class AktivasiWargaController extends Controller
{
    public function create(string $warga)
    {
        abort_unless(User::menungguAktivasiWarga()->whereKey($warga)->exists(), 404);

        return response()->view('auth.aktivasi-warga', compact('warga'))->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, string $warga)
    {
        $kode = $request->input('kode_aktivasi');
        $formatKode = '/\A[a-f0-9]{64}\z/';
        try {
            $data = $request->validate([
                'kode_aktivasi' => ['required', 'string', 'regex:'.$formatKode],
                'password' => ['required', 'confirmed', AturanKataSandi::min(8)],
            ], [
                'kode_aktivasi.required' => 'Buka tautan aktivasi yang diberikan kader atau masukkan kodenya.',
                'kode_aktivasi.regex' => 'Kode aktivasi tidak valid.',
                'password.required' => 'Buat kata sandi untuk akunmu.',
                'password.min' => 'Gunakan kata sandi dengan minimal 8 karakter.',
                'password.confirmed' => 'Kedua kata sandi harus sama.',
            ]);

            $user = DB::transaction(function () use ($warga, $data) {
                $user = User::menungguAktivasiWarga()->lockForUpdate()->findOrFail($warga);
                if (!Password::tokenExists($user, $data['kode_aktivasi'])) {
                    throw ValidationException::withMessages(['kode_aktivasi' => 'Kode aktivasi tidak berlaku. Minta kader membuat tautan baru.']);
                }

                $user->forceFill(['password' => $data['password'], 'menunggu_aktivasi' => false])->save();
                Password::deleteToken($user);

                return $user;
            }, 3);
        } catch (ValidationException $e) {
            $fragment = is_string($kode) && preg_match($formatKode, $kode) ? '#'.$kode : '';
            throw $e->redirectTo(route('aktivasi-warga.create', $warga).$fragment);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('pencari.profil')->with('sukses', 'Akun siap dipakai. Lengkapi profil dan pilih persetujuan penggunaan datamu.');
    }
}
