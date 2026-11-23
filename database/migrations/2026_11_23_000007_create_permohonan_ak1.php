<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonan_ak1', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('pemohon_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('desa_id')->constrained('desa')->restrictOnDelete();
            $table->string('status', 24);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonan_ak1');
    }
};
