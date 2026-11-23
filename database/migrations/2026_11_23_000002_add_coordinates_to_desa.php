<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->decimal('lintang', 8, 5)->nullable();
            $table->decimal('bujur', 8, 5)->nullable();
        });

        $titik = require database_path('data/titik-desa-pakis-2026.php');
        foreach ($titik as $kecamatan => $desa) {
            foreach ($desa as $nama => [$lintang, $bujur]) {
                DB::table('desa')->where(['nama' => $nama, 'kecamatan' => $kecamatan])
                    ->update(['lintang' => $lintang, 'bujur' => $bujur]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('desa', function (Blueprint $table) {
            $table->dropColumn(['lintang', 'bujur']);
        });
    }
};
