<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usaha', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        // InnoDB can replace the original foreign-key index with the unique one.
        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb'], true)
            && !Schema::hasIndex('usaha', 'usaha_user_id_foreign')) {
            Schema::table('usaha', function (Blueprint $table) {
                $table->index('user_id', 'usaha_user_id_foreign');
            });
        }

        Schema::table('usaha', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
