<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilPencari extends Model
{
    protected $table = 'profil_pencari';

    protected $fillable = [
        'user_id', 'tanggal_lahir', 'jenis_kelamin', 'pendidikan_terakhir', 'jurusan',
        'status_kerja', 'bersedia_paruh_waktu', 'persetujuan_data_at', 'didaftarkan_oleh',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'bersedia_paruh_waktu' => 'boolean',
        'persetujuan_data_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function keahlian(): BelongsToMany
    {
        return $this->belongsToMany(Keahlian::class, 'pencari_keahlian', 'profil_id', 'keahlian_id')
            ->withPivot('tingkat');
    }

    public function lamaran(): HasMany
    {
        return $this->hasMany(Lamaran::class, 'profil_id');
    }

    /** Profil aktif = warga sudah menyetujui penggunaan data (F-03). */
    public function isAktif(): bool
    {
        return $this->persetujuan_data_at !== null;
    }

    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->whereNotNull('persetujuan_data_at');
    }
}
