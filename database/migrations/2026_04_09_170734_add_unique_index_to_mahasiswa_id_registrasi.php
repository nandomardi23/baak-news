<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add unique index on id_registrasi_mahasiswa.
     * Required for MySQL upsert (ON DUPLICATE KEY UPDATE) to work correctly
     * when syncing from NeoFeeder using id_registrasi_mahasiswa as the key.
     */
    public function up(): void
    {
        // First, clean up any NULL id_registrasi_mahasiswa to avoid unique index issues
        // (only local-created records would have NULL; synced records always have a value)
        \Illuminate\Support\Facades\DB::statement("
            UPDATE mahasiswa 
            SET id_registrasi_mahasiswa = CONCAT('local-', id) 
            WHERE id_registrasi_mahasiswa IS NULL
        ");

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->unique('id_registrasi_mahasiswa', 'mahasiswa_id_reg_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropUnique('mahasiswa_id_reg_unique');
        });
    }
};
