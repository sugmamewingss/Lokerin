<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lowongan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usaha_id')->constrained('usaha')->cascadeOnDelete();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->enum('jenis_kerja', ['penuh_waktu', 'paruh_waktu', 'harian'])->default('penuh_waktu');
            $table->enum('pendidikan_min', ['SD', 'SMP', 'SMA', 'SMK', 'MA', 'D3', 'S1'])->default('SMP');
            // Kosong = terbuka untuk semua jurusan
            $table->string('jurusan_target', 100)->nullable();
            $table->unsignedSmallInteger('kuota')->default(1);
            $table->unsignedInteger('gaji_min')->nullable();
            $table->unsignedInteger('gaji_max')->nullable();
            $table->enum('status', ['draf', 'aktif', 'ditutup'])->default('aktif');
            $table->date('tanggal_tutup')->nullable();
            $table->timestamps();
        });

        Schema::create('lowongan_keahlian', function (Blueprint $table) {
            $table->foreignId('lowongan_id')->constrained('lowongan')->cascadeOnDelete();
            $table->foreignId('keahlian_id')->constrained('keahlian')->cascadeOnDelete();
            $table->boolean('wajib')->default(true);
            $table->primary(['lowongan_id', 'keahlian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lowongan_keahlian');
        Schema::dropIfExists('lowongan');
    }
};
