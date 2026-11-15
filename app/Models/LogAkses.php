<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAkses extends Model
{
    protected $table = 'log_akses';

    public $timestamps = false;

    protected $fillable = ['user_id', 'aksi', 'objek', 'waktu'];

    protected $casts = ['waktu' => 'datetime'];

    /** Catat aksi penting untuk jejak audit. */
    public static function catat(string $aksi, string $objek): void
    {
        static::create(['user_id' => auth()->id(), 'aksi' => $aksi, 'objek' => $objek, 'waktu' => now()]);
    }
}
