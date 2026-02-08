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
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreignId('dosen_wali_id')->nullable()->constrained('dosen')->nullOnDelete();
        });

        Schema::table('krs_detail', function (Blueprint $table) {
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->string('nama_dosen')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->dropColumn(['dosen_id', 'nama_dosen']);
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropForeign(['dosen_wali_id']);
            $table->dropColumn('dosen_wali_id');
        });
    }
};
