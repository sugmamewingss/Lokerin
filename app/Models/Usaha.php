<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usaha extends Model
{
    protected $table = 'usaha';

    protected $fillable = [
        'user_id', 'nama_usaha', 'jenis_usaha', 'desa_id', 'alamat',
        'status_verifikasi', 'diverifikasi_oleh', 'diverifikasi_at',
    ];

    protected $casts = ['diverifikasi_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function lowongan(): HasMany
    {
        return $this->hasMany(Lowongan::class);
    }

    public function scopeTerverifikasi(Builder $q): Builder
    {
        return $q->where('status_verifikasi', 'terverifikasi');
    }
}
