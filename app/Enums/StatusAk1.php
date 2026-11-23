<?php

namespace App\Enums;

enum StatusAk1: string
{
    case Diajukan = 'diajukan';
    case PerluDilengkapi = 'perlu_dilengkapi';
    case Dirujuk = 'dirujuk';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Diajukan => 'Menunggu pemeriksaan kader',
            self::PerluDilengkapi => 'Perlu dilengkapi',
            self::Dirujuk => 'Dirujuk ke layanan resmi',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function tindakan(): string
    {
        return match ($this) {
            self::Diajukan => 'Ajukan kembali',
            self::PerluDilengkapi => 'Minta kelengkapan',
            self::Dirujuk => 'Catat rujukan',
            self::Selesai => 'Konfirmasi kartu diterima',
            self::Dibatalkan => 'Batalkan permohonan',
        };
    }

    public function petunjuk(): string
    {
        return match ($this) {
            self::Diajukan => 'Kader memeriksa kesiapan warga untuk mengurus AK/1.',
            self::PerluDilengkapi => 'Siapkan berkas sesuai panduan. Ajukan kembali ketika berkas sudah siap.',
            self::Dirujuk => 'Ikuti petunjuk layanan resmi. Konfirmasi setelah kartu diterima.',
            self::Selesai => 'Warga telah mengonfirmasi bahwa kartu diterima.',
            self::Dibatalkan => 'Warga telah membatalkan permohonan.',
        };
    }

    /** @return list<self> */
    public function langkahBerikutnya(bool $kader): array
    {
        if ($kader) {
            return match ($this) {
                self::Diajukan => [self::PerluDilengkapi, self::Dirujuk],
                self::PerluDilengkapi => [self::Diajukan],
                default => [],
            };
        }

        return match ($this) {
            self::Diajukan => [self::Dibatalkan],
            self::PerluDilengkapi => [self::Diajukan, self::Dibatalkan],
            self::Dirujuk => [self::Selesai, self::Dibatalkan],
            default => [],
        };
    }
}
