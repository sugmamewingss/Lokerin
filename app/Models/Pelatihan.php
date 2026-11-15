<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pelatihan extends Model
{
    protected $table = 'pelatihan';

    protected $fillable = ['penyelenggara', 'judul', 'keahlian_id', 'jadwal', 'lokasi', 'kuota'];

    public function keahlian(): BelongsTo
    {
        return $this->belongsTo(Keahlian::class);
    }
}
