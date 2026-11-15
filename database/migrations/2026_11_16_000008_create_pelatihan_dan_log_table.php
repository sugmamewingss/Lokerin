<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelatihan', function (Blueprint $table) {
            $table->id();
            $table->string('penyelenggara', 150);
            $table->string('judul', 150);
            $table->foreignId('keahlian_id')->constrained('keahlian')->cascadeOnDelete();
            $table->string('jadwal', 100)->nullable();
            $table->string('lokasi', 150)->nullable();
            $table->unsignedSmallInteger('kuota')->nullable();
            $table->timestamps();
        });

        Schema::create('log_akses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi', 50);   // contoh: verifikasi_usaha, ubah_status_lamaran
            $table->string('objek', 100); // contoh: usaha#3, lamaran#12
            $table->timestamp('waktu')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_akses');
        Schema::dropIfExists('pelatihan');
    }
};
