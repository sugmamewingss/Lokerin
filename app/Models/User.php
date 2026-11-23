<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_PENCARI = 'pencari';
    public const ROLE_USAHA = 'usaha';
    public const ROLE_KADER = 'kader';
    public const ROLE_DISNAKER = 'disnaker';

    protected $fillable = ['name', 'email', 'no_hp', 'password', 'role', 'desa_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'menunggu_aktivasi' => 'boolean',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }

    public function profilPencari(): HasOne
    {
        return $this->hasOne(ProfilPencari::class);
    }

    public function usaha(): HasOne
    {
        return $this->hasOne(Usaha::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function scopeMenungguAktivasiWarga(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_PENCARI)->where('menunggu_aktivasi', true);
    }

    public static function emailUntukNomorHp(string $nomor): string
    {
        return preg_replace('/\D/', '', $nomor).'@hp.lokerin.local';
    }

    /** Halaman awal setelah login, sesuai peran. */
    public function homeRoute(): string
    {
        return match ($this->role) {
            self::ROLE_USAHA => 'usaha.beranda',
            self::ROLE_KADER => 'kader.dasbor',
            self::ROLE_DISNAKER => 'disnaker.dasbor',
            default => 'pencari.beranda',
        };
    }
}
