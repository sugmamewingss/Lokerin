<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->foreignId('desa_id')->nullable()->constrained('desa')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pelatihan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('desa_id');
        });
    }
};
