<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    protected $table = 'lowongan';

    protected $fillable = [
        'usaha_id', 'judul', 'deskripsi', 'jenis_kerja', 'pendidikan_min', 'jurusan_target',
        'kuota', 'gaji_min', 'gaji_max', 'status', 'tanggal_tutup',
    ];

    protected $casts = ['tanggal_tutup' => 'date'];

    public function usaha(): BelongsTo
    {
        return $this->belongsTo(Usaha::class);
    }

    public function keahlian(): BelongsToMany
    {
        return $this->belongsToMany(Keahlian::class, 'lowongan_keahlian', 'lowongan_id', 'keahlian_id')
            ->withPivot('wajib');
    }

    public function keahlianWajib(): BelongsToMany
    {
        return $this->keahlian()->wherePivot('wajib', true);
    }

    public function lamaran(): HasMany
    {
        return $this->hasMany(Lamaran::class);
    }

    /** Lowongan yang boleh tampil dan dicocokkan: aktif, belum lewat tanggal tutup, usaha terverifikasi (F-05). */
    public function scopeTampil(Builder $q): Builder
    {
        return $q->where('status', 'aktif')
            ->where(fn ($w) => $w->whereNull('tanggal_tutup')->orWhereDate('tanggal_tutup', '>=', now()))
            ->whereHas('usaha', fn ($u) => $u->where('status_verifikasi', 'terverifikasi'));
    }
}
