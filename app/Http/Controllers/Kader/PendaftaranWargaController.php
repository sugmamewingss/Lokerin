<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Models\LogAkses;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PendaftaranWargaController extends Controller
{
    public function index(Request $request)
    {
        $desa = $request->user()->desa;
        abort_unless($desa, 403, 'Akun kader belum terhubung ke desa.');
        $warga = User::menungguAktivasiWarga()->where('desa_id', $desa->id)
            ->latest('id')->paginate(12, ['id', 'name', 'no_hp']);

        return response()->view('kader.warga', compact('desa', 'warga'))->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request)
    {
        $kader = $request->user();
        abort_unless($kader->desa_id, 403, 'Akun kader belum terhubung ke desa.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'no_hp' => ['required', 'string', 'regex:/\A08[0-9]{8,11}\z/', 'unique:users,no_hp'],
            'desa_id' => ['prohibited'],
        ], [
            'name.required' => 'Isi nama lengkap warga.',
            'no_hp.required' => 'Isi nomor HP warga.',
            'no_hp.regex' => 'Gunakan nomor HP berawalan 08 dengan 10–13 angka tanpa spasi.',
            'no_hp.unique' => 'Nomor HP sudah terdaftar.',
            'desa_id.prohibited' => 'Pendaftaran menggunakan desa akun kader.',
        ]);

        try {
            [$warga, $kode] = DB::transaction(function () use ($kader, $data) {
                $warga = new User([
                    'name' => $data['name'],
                    'no_hp' => $data['no_hp'],
                    'email' => User::emailUntukNomorHp($data['no_hp']),
                    'role' => User::ROLE_PENCARI,
                    'desa_id' => $kader->desa_id,
                    'password' => Str::random(40),
                ]);
                $warga->menunggu_aktivasi = true;
                $warga->save();
                $profil = $warga->profilPencari()->create(['didaftarkan_oleh' => $kader->id]);
                $kode = Password::createToken($warga);
                LogAkses::catat('pendaftaran_warga', "profil#{$profil->id}");

                return [$warga, $kode];
            }, 3);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['no_hp' => 'Nomor HP sudah terdaftar.']);
        }

        return $this->serahkanTautan($warga, $kode);
    }

    public function perbaruiTautan(Request $request, string $warga)
    {
        $desaId = $request->user()->desa_id;
        abort_unless($desaId, 403, 'Akun kader belum terhubung ke desa.');

        [$warga, $kode] = DB::transaction(function () use ($desaId, $warga) {
            $warga = User::menungguAktivasiWarga()->where('desa_id', $desaId)->lockForUpdate()->findOrFail($warga);
            $kode = Password::createToken($warga);
            LogAkses::catat('perbarui_aktivasi_warga', "user#{$warga->id}");

            return [$warga, $kode];
        }, 3);

        return $this->serahkanTautan($warga, $kode);
    }

    private function serahkanTautan(User $warga, string $kode)
    {
        // Fragment membawa kode ke formulir tanpa memasukkannya ke permintaan GET.
        $tautan = route('aktivasi-warga.create', $warga).'#'.$kode;
        $kedaluwarsaMenit = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return response()->view('kader.tautan-aktivasi-warga', compact('warga', 'tautan', 'kedaluwarsaMenit'))
            ->header('Cache-Control', 'private, no-store');
    }
}
