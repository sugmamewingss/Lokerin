<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lamaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lowongan_id')->constrained('lowongan')->cascadeOnDelete();
            $table->foreignId('profil_id')->constrained('profil_pencari')->cascadeOnDelete();
            $table->unsignedTinyInteger('skor_cocok')->default(0);
            $table->enum('status', ['dikirim', 'dilihat', 'wawancara', 'diterima', 'ditolak', 'dibatalkan'])->default('dikirim');
            $table->timestamp('dikirim_at')->useCurrent();
            $table->timestamps();
            $table->unique(['lowongan_id', 'profil_id']); // satu orang satu lamaran per lowongan
        });

        Schema::create('penempatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lamaran_id')->unique()->constrained('lamaran')->cascadeOnDelete();
            $table->date('tanggal_mulai')->nullable();
            $table->enum('jenis_kontrak', ['tetap', 'kontrak', 'paruh_waktu', 'harian'])->nullable();
            $table->boolean('terdaftar_bpjs')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penempatan');
        Schema::dropIfExists('lamaran');
    }
};
