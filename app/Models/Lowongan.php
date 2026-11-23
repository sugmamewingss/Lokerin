<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Lowongan extends Model
{
    public const JENIS_KERJA = ['penuh_waktu' => 'Penuh waktu', 'paruh_waktu' => 'Paruh waktu', 'harian' => 'Harian'];

    public const STATUS = ['draf' => 'Draf', 'aktif' => 'Aktif', 'ditutup' => 'Ditutup'];

    protected $table = 'lowongan';

    protected $fillable = [
        'usaha_id', 'judul', 'deskripsi', 'jenis_kerja', 'pendidikan_min', 'jurusan_target',
        'kuota', 'gaji_min', 'gaji_max', 'status', 'tanggal_tutup',
    ];

    protected $casts = ['tanggal_tutup' => 'date', 'diterbitkan_at' => 'datetime'];

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

    public function penempatan(): HasManyThrough
    {
        return $this->hasManyThrough(Penempatan::class, Lamaran::class, 'lowongan_id', 'lamaran_id');
    }

    public function catatPenerbitanPertama(): void
    {
        if ($this->status === 'aktif') {
            $this->diterbitkan_at ??= now();
        }
    }

    public function rentangGaji(): ?string
    {
        $min = $this->gaji_min === null ? null : 'Rp'.number_format($this->gaji_min, 0, ',', '.');
        $max = $this->gaji_max === null ? null : 'Rp'.number_format($this->gaji_max, 0, ',', '.');

        return match (true) {
            $min !== null && $max !== null => $min === $max ? $min : "$min – $max",
            $min !== null => "Mulai $min",
            $max !== null => "Hingga $max",
            default => null,
        };
    }

    /** Lowongan yang boleh tampil, dicocokkan, dan dilamar (F-05). */
    public function scopeTampil(Builder $q): Builder
    {
        $jumlahPenempatan = Penempatan::query()->selectRaw('COUNT(*)')
            ->join('lamaran', 'lamaran.id', '=', 'penempatan.lamaran_id')
            ->whereColumn('lamaran.lowongan_id', 'lowongan.id');

        return $q->where('status', 'aktif')
            ->where(fn ($w) => $w->whereNull('tanggal_tutup')->orWhereDate('tanggal_tutup', '>=', self::tanggalDiPakis()))
            ->where('lowongan.kuota', '>', $jumlahPenempatan)
            ->whereHas('usaha', fn ($u) => $u->where('status_verifikasi', 'terverifikasi'));
    }

    public static function tanggalDiPakis(): string
    {
        return now('Asia/Jakarta')->toDateString();
    }
}
