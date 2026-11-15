<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kecamatan', 100)->default('Pakis');
            $table->decimal('luas_km2', 6, 2)->nullable();
            $table->unsignedInteger('penduduk')->nullable();
            $table->unsignedTinyInteger('jarak_ke_kecamatan_km')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa');
    }
};
