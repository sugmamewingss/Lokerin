<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_hp', 20)->nullable()->unique()->after('email');
            // pencari | usaha | kader | disnaker
            $table->string('role', 20)->default('pencari')->after('password')->index();
            $table->foreignId('desa_id')->nullable()->after('role')->constrained('desa')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('desa_id');
            $table->dropColumn(['no_hp', 'role']);
        });
    }
};
