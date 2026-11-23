<?php

use App\Http\Controllers\Auth\AktivasiWargaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Disnaker\CommandCenterController;
use App\Http\Controllers\Disnaker\DasborController as DisnakerDasbor;
use App\Http\Controllers\Disnaker\KeahlianController;
use App\Http\Controllers\Disnaker\PelatihanController as DisnakerPelatihan;
use App\Http\Controllers\Disnaker\SiapkerjaController;
use App\Http\Controllers\Kader\DasborController as KaderDasbor;
use App\Http\Controllers\Kader\PendaftaranWargaController;
use App\Http\Controllers\Kader\PublikasiLowonganController;
use App\Http\Controllers\Kader\VerifikasiController;
use App\Http\Controllers\Pencari\BerandaController as PencariBeranda;
use App\Http\Controllers\Pencari\LamaranController as PencariLamaran;
use App\Http\Controllers\Pencari\ProfilController;
use App\Http\Controllers\PelatihanController as KatalogPelatihan;
use App\Http\Controllers\PermohonanAk1Controller;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\Usaha\BerandaController as UsahaBeranda;
use App\Http\Controllers\Usaha\LowonganController as UsahaLowongan;
use App\Http\Controllers\Usaha\PelamarController;
use App\Http\Controllers\Usaha\ProfilController as UsahaProfil;
use Illuminate\Support\Facades\Route;

/*
| Peta halaman mengikuti Rancangan Lokerin Tabel 7.1.
| Kode di belakang tanda "TODO" adalah bagian yang dikerjakan anggota sesuai Bab 8.2.
*/

// ---------- Publik ----------
Route::get('/', [PublikController::class, 'beranda'])->name('beranda');
Route::view('/privasi', 'publik.privasi')->name('privasi');
Route::view('/panduan-ak1', 'publik.panduan-ak1')->name('ak1.panduan');
Route::get('/lowongan', [PublikController::class, 'lowongan'])->name('lowongan.index');
Route::get('/lowongan/{lowongan}', [PublikController::class, 'detail'])->name('lowongan.detail');
Route::get('/pelatihan', [KatalogPelatihan::class, 'index'])->name('pelatihan.index');
Route::get('/pelatihan/{pelatihan}', [KatalogPelatihan::class, 'show'])->name('pelatihan.show');

// ---------- Autentikasi ----------
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store']);
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store']);
    Route::get('/aktivasi-warga/{warga}', [AktivasiWargaController::class, 'create'])->whereNumber('warga')->name('aktivasi-warga.create');
    Route::post('/aktivasi-warga/{warga}', [AktivasiWargaController::class, 'store'])->whereNumber('warga')
        ->middleware('throttle:10,1')->name('aktivasi-warga.store');
});
Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/beranda', fn () => redirect()->route(auth()->user()->homeRoute()))
    ->middleware('auth')->name('home');

Route::middleware(['auth', 'role:pencari,kader'])->prefix('ak1')->name('ak1.')->group(function () {
    Route::get('/', [PermohonanAk1Controller::class, 'index'])->name('index');
    Route::post('/', [PermohonanAk1Controller::class, 'store'])->name('store');
    Route::get('/{permohonan}', [PermohonanAk1Controller::class, 'show'])->whereUuid('permohonan')->name('show');
    Route::patch('/{permohonan}', [PermohonanAk1Controller::class, 'update'])->whereUuid('permohonan')->name('update');
});

// ---------- Pencari kerja ----------
Route::middleware(['auth', 'role:pencari'])->prefix('pencari')->name('pencari.')->group(function () {
    Route::get('/', [PencariBeranda::class, 'index'])->name('beranda');
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update']);
    Route::delete('/profil/persetujuan', [ProfilController::class, 'tarikPersetujuan'])->name('persetujuan.tarik');
    Route::get('/lamaran', [PencariLamaran::class, 'index'])->name('lamaran');
    Route::post('/lowongan/{lowongan}/lamar', [PencariLamaran::class, 'store'])->name('lamar');
    Route::patch('/lamaran/{lamaran}/batal', [PencariLamaran::class, 'batalkan'])->name('lamaran.batal');
});

// ---------- Usaha ----------
Route::middleware(['auth', 'role:usaha'])->prefix('usaha')->name('usaha.')->group(function () {
    Route::get('/', [UsahaBeranda::class, 'index'])->name('beranda');
    Route::get('/profil', [UsahaProfil::class, 'edit'])->name('profil');
    Route::put('/profil', [UsahaProfil::class, 'update'])->name('profil.update');
    Route::resource('lowongan', UsahaLowongan::class)->only(['create', 'store', 'edit', 'update']);
    Route::get('/lowongan/{lowongan}/pelamar', [PelamarController::class, 'index'])->name('pelamar.index');
    Route::patch('/lowongan/{lowongan}/pelamar/{lamaran}', [PelamarController::class, 'update'])->name('pelamar.update');
});

// ---------- Kader desa ----------
Route::middleware(['auth', 'role:kader'])->prefix('kader')->name('kader.')->group(function () {
    Route::get('/', [KaderDasbor::class, 'index'])->name('dasbor');
    Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi');
    Route::get('/publikasi-lowongan', [PublikasiLowonganController::class, 'index'])->name('publikasi-lowongan');
    Route::post('/verifikasi/{usaha}', [VerifikasiController::class, 'update'])->name('verifikasi.update');
    Route::get('/warga', [PendaftaranWargaController::class, 'index'])->name('warga.index');
    Route::post('/warga', [PendaftaranWargaController::class, 'store'])->name('warga.store');
    Route::post('/warga/{warga}/tautan-baru', [PendaftaranWargaController::class, 'perbaruiTautan'])->whereNumber('warga')->name('warga.tautan');
});

// ---------- Disnaker ----------
Route::middleware(['auth', 'role:disnaker'])->prefix('disnaker')->name('disnaker.')->group(function () {
    Route::get('/', [DisnakerDasbor::class, 'index'])->name('dasbor');
    Route::get('/siapkerja', [SiapkerjaController::class, 'index'])->name('siapkerja.index');
    Route::prefix('command-center')->name('command-center.')->group(function () {
        Route::get('/', [CommandCenterController::class, 'index'])->name('index');
        Route::post('/', [CommandCenterController::class, 'store'])->name('store');
        Route::get('/{rekap}/unduh', [CommandCenterController::class, 'download'])->whereNumber('rekap')->name('download');
    });
    Route::resource('pelatihan', DisnakerPelatihan::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::patch('/pelatihan/{pelatihan}/pulihkan', [DisnakerPelatihan::class, 'pulihkan'])->withTrashed()->name('pelatihan.pulihkan');
    Route::resource('keahlian', KeahlianController::class)->only(['index', 'store', 'update']);
});
