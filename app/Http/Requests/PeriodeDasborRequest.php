<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class PeriodeDasborRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mulai' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.now('Asia/Jakarta')->toDateString()],
        ];
    }

    public function messages(): array
    {
        return [
            'mulai.date_format' => 'Pilih tanggal mulai uji coba yang valid.',
            'mulai.before_or_equal' => 'Tanggal mulai uji coba maksimal hari ini.',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route($this->route()->getName());
    }

    public function awalUjiCoba(): ?CarbonImmutable
    {
        $tanggal = $this->validated('mulai');

        return $tanggal ? CarbonImmutable::createFromFormat('!Y-m-d', $tanggal, 'Asia/Jakarta') : null;
    }
}
