<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lamaran extends Model
{
    protected $table = 'lamaran';

    protected $fillable = ['lowongan_id', 'profil_id', 'skor_cocok', 'status', 'dikirim_at'];

    protected $casts = ['dikirim_at' => 'datetime'];

    /** Urutan status yang sah (Rancangan Bab 4.4). */
    public const TRANSISI = [
        'dikirim'   => ['dilihat', 'dibatalkan'],
        'dilihat'   => ['wawancara', 'ditolak', 'dibatalkan'],
        'wawancara' => ['diterima', 'ditolak'],
        'diterima'  => [],
        'ditolak'   => [],
        'dibatalkan' => [],
    ];

    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class);
    }

    public function profil(): BelongsTo
    {
        return $this->belongsTo(ProfilPencari::class, 'profil_id');
    }

    public function penempatan(): HasOne
    {
        return $this->hasOne(Penempatan::class);
    }

    public function bolehPindahKe(string $statusBaru): bool
    {
        return in_array($statusBaru, self::TRANSISI[$this->status] ?? [], true);
    }

    /** Nomor HP pelamar baru boleh dilihat usaha mulai tahap wawancara (privasi, Tabel 3.2). */
    public function kontakTerbuka(): bool
    {
        return in_array($this->status, ['wawancara', 'diterima'], true);
    }
}
