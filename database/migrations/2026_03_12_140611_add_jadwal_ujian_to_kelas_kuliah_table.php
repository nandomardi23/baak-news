<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kelas_kuliah', function (Blueprint $table) {
            $table->date('tanggal_uts')->nullable()->after('nama_dosen');
            $table->date('tanggal_uas')->nullable()->after('tanggal_uts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelas_kuliah', function (Blueprint $table) {
            $table->dropColumn(['tanggal_uts', 'tanggal_uas']);
        });
    }
};
