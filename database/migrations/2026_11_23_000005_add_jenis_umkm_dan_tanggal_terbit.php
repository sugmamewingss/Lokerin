<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usaha', function (Blueprint $table) {
            $table->boolean('is_umkm')->nullable();
        });

        Schema::table('lowongan', function (Blueprint $table) {
            $table->timestamp('diterbitkan_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lowongan', function (Blueprint $table) {
            $table->dropColumn('diterbitkan_at');
        });

        Schema::table('usaha', function (Blueprint $table) {
            $table->dropColumn('is_umkm');
        });
    }
};
