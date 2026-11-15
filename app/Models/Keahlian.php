<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keahlian extends Model
{
    protected $table = 'keahlian';

    protected $fillable = ['nama', 'kategori'];

    public function pelatihan(): HasMany
    {
        return $this->hasMany(Pelatihan::class);
    }
}
