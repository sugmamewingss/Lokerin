<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_command_center', function (Blueprint $table) {
            $table->id();
            $table->char('sidik_data', 64)->unique();
            $table->json('data');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_command_center');
    }
};
