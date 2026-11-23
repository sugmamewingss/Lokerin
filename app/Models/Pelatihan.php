<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelatihan extends Model
{
    use SoftDeletes;

    public const JENIS_PENYELENGGARA = ['blk' => 'BLK', 'lpk' => 'LPK', 'smk' => 'SMK', 'lainnya' => 'Lainnya'];

    protected $table = 'pelatihan';

    protected $fillable = ['penyelenggara', 'jenis_penyelenggara', 'judul', 'keahlian_id', 'jadwal', 'lokasi', 'desa_id', 'kuota'];

    public function keahlian(): BelongsTo
    {
        return $this->belongsTo(Keahlian::class);
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class);
    }
}
