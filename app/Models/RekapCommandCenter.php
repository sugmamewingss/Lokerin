<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapCommandCenter extends Model
{
    protected $table = 'rekap_command_center';

    public $timestamps = false;

    protected $fillable = ['sidik_data', 'data', 'disetujui_oleh', 'disetujui_pada'];

    protected $casts = ['data' => 'array', 'disetujui_pada' => 'immutable_datetime'];
}
