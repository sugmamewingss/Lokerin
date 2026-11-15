<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desa extends Model
{
    protected $table = 'desa';

    protected $fillable = ['nama', 'kecamatan', 'luas_km2', 'penduduk', 'jarak_ke_kecamatan_km'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function usaha(): HasMany
    {
        return $this->hasMany(Usaha::class);
    }
}
