<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add unique composite index on krs_detail to enable batch upsert.
     * Also add id_registrasi_mahasiswa + id_semester unique to krs for batch upsert.
     */
    public function up(): void
    {
        // Add unique index to krs for upsert by NeoFeeder IDs
        Schema::table('krs', function (Blueprint $table) {
            $table->unique(['id_registrasi_mahasiswa', 'id_semester'], 'krs_reg_sem_unique');
        });

        // Add unique index to krs_detail for upsert
        Schema::table('krs_detail', function (Blueprint $table) {
            $table->unique(['krs_id', 'id_matkul'], 'krs_detail_krs_matkul_unique');
        });
    }

    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropUnique('krs_reg_sem_unique');
        });

        Schema::table('krs_detail', function (Blueprint $table) {
            $table->dropUnique('krs_detail_krs_matkul_unique');
        });
    }
};
