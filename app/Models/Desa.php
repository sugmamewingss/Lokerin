<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Desa extends Model
{
    protected $table = 'desa';

    protected $fillable = ['nama', 'kecamatan', 'luas_km2', 'penduduk', 'jarak_ke_kecamatan_km', 'lintang', 'bujur'];

    protected $casts = ['lintang' => 'float', 'bujur' => 'float'];

    public function simpulAk1(): bool
    {
        return $this->nama === 'Pakiskembar' && $this->kecamatan === 'Pakis';
    }

    public function informasiBanjir(): ?array
    {
        if ($this->kecamatan !== 'Pakis') {
            return null;
        }

        $data = require resource_path('data/banjir-pakis.php');
        if (!in_array($this->nama, $data['ancaman']['desa'], true)) {
            return null;
        }

        return [
            'acuan' => $data['ancaman'],
            'kejadian' => array_filter($data['kejadian'], fn (array $kejadian) => in_array($this->nama, $kejadian['desa'], true)),
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function usaha(): HasMany
    {
        return $this->hasMany(Usaha::class);
    }

    public function profilPencari(): HasManyThrough
    {
        return $this->hasManyThrough(ProfilPencari::class, User::class, 'desa_id', 'user_id');
    }

    public function lowongan(): HasManyThrough
    {
        return $this->hasManyThrough(Lowongan::class, Usaha::class, 'desa_id', 'usaha_id');
    }
}
