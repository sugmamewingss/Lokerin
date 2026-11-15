<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_pencari', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->enum('pendidikan_terakhir', ['SD', 'SMP', 'SMA', 'SMK', 'MA', 'D3', 'S1'])->nullable();
            $table->string('jurusan', 100)->nullable();
            $table->enum('status_kerja', ['belum_bekerja', 'paruh_waktu', 'bekerja'])->default('belum_bekerja');
            $table->boolean('bersedia_paruh_waktu')->default(true);
            // Kosong = warga belum menyetujui penggunaan data, profil belum aktif (F-03)
            $table->timestamp('persetujuan_data_at')->nullable();
            // Diisi kalau warga didaftarkan kader desa (F-13)
            $table->foreignId('didaftarkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pencari_keahlian', function (Blueprint $table) {
            $table->foreignId('profil_id')->constrained('profil_pencari')->cascadeOnDelete();
            $table->foreignId('keahlian_id')->constrained('keahlian')->cascadeOnDelete();
            $table->enum('tingkat', ['dasar', 'menengah', 'mahir'])->default('dasar');
            $table->primary(['profil_id', 'keahlian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencari_keahlian');
        Schema::dropIfExists('profil_pencari');
    }
};
