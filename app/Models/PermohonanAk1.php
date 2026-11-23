<?php

namespace App\Models;

use App\Enums\StatusAk1;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermohonanAk1 extends Model
{
    use HasUuids;

    protected $table = 'permohonan_ak1';

    protected $fillable = ['pemohon_id', 'desa_id', 'status'];

    protected $casts = ['status' => StatusAk1::class];

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function scopeDapatDilihatOleh(Builder $query, User $user): Builder
    {
        return $user->hasRole(User::ROLE_KADER)
            ? $query->where('desa_id', $user->desa_id)
            : $query->where('pemohon_id', $user->id);
    }

    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->whereNotIn('status', [StatusAk1::Selesai->value, StatusAk1::Dibatalkan->value]);
    }

    public function riwayat()
    {
        return LogAkses::query()->where('objek', 'permohonan_ak1#'.$this->getKey())->orderBy('id')->get();
    }

    public function catatStatus(): void
    {
        LogAkses::catat($this->status->value, 'permohonan_ak1#'.$this->getKey());
    }
}
