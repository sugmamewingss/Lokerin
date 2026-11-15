<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penempatan extends Model
{
    protected $table = 'penempatan';

    protected $fillable = ['lamaran_id', 'tanggal_mulai', 'jenis_kontrak', 'terdaftar_bpjs'];

    protected $casts = ['tanggal_mulai' => 'date', 'terdaftar_bpjs' => 'boolean'];

    public function lamaran(): BelongsTo
    {
        return $this->belongsTo(Lamaran::class);
    }
}
