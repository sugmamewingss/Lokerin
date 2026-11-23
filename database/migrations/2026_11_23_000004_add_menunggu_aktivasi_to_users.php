<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('menunggu_aktivasi')->default(false);
        });
    }

    public function down(): void
    {
        if (DB::table('users')->where('menunggu_aktivasi', true)->exists()) {
            throw new RuntimeException('Selesaikan aktivasi akun warga yang menunggu sebelum rollback.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('menunggu_aktivasi');
        });
    }
};
