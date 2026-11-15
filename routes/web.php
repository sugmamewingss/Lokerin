<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Disnaker\DasborController as DisnakerDasbor;
use App\Http\Controllers\Kader\DasborController as KaderDasbor;
use App\Http\Controllers\Kader\VerifikasiController;
use App\Http\Controllers\Pencari\BerandaController as PencariBeranda;
use App\Http\Controllers\Pencari\LamaranController as PencariLamaran;
use App\Http\Controllers\Pencari\ProfilController;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\Usaha\BerandaController as UsahaBeranda;
use Illuminate\Support\Facades\Route;

/*
| Peta halaman mengikuti Rancangan Lokerin Tabel 7.1.
| Kode di belakang tanda "TODO" adalah bagian yang dikerjakan anggota sesuai Bab 8.2.
*/

// ---------- Publik ----------
Route::get('/', [PublikController::class, 'beranda'])->name('beranda');
Route::get('/lowongan', [PublikController::class, 'lowongan'])->name('lowongan.index');
Route::get('/lowongan/{lowongan}', [PublikController::class, 'detail'])->name('lowongan.detail');

// ---------- Autentikasi ----------
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store']);
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store']);
});
Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/beranda', fn () => redirect()->route(auth()->user()->homeRoute()))
    ->middleware('auth')->name('home');

// ---------- Pencari kerja ----------
Route::middleware(['auth', 'role:pencari'])->prefix('pencari')->name('pencari.')->group(function () {
    Route::get('/', [PencariBeranda::class, 'index'])->name('beranda');
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update']);
    Route::get('/lamaran', [PencariLamaran::class, 'index'])->name('lamaran');
    Route::post('/lowongan/{lowongan}/lamar', [PencariLamaran::class, 'store'])->name('lamar');
    // TODO (Rakapaksi): batalkan lamaran, halaman pelatihan
});

// ---------- Usaha ----------
Route::middleware(['auth', 'role:usaha'])->prefix('usaha')->name('usaha.')->group(function () {
    Route::get('/', [UsahaBeranda::class, 'index'])->name('beranda');
    // TODO (Adefriko): profil usaha, pasang/kelola lowongan, daftar pelamar + ubah status (F-04, F-09, F-10)
});

// ---------- Kader desa ----------
Route::middleware(['auth', 'role:kader'])->prefix('kader')->name('kader.')->group(function () {
    Route::get('/', [KaderDasbor::class, 'index'])->name('dasbor');
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi');
    Route::post('/verifikasi/{usaha}', [VerifikasiController::class, 'update'])->name('verifikasi.update');
    // TODO (Rakapaksi/Rosihan): daftarkan warga atas nama warga (F-13)
});

// ---------- Disnaker ----------
Route::middleware(['auth', 'role:disnaker'])->prefix('disnaker')->name('disnaker.')->group(function () {
    Route::get('/', [DisnakerDasbor::class, 'index'])->name('dasbor');
    // TODO (Muflih): grafik dasbor; kelola pelatihan dan keahlian (F-15)
});
