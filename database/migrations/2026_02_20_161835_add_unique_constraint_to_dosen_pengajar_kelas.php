<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Add unique constraint on dosen_pengajar_kelas for batch upsert support.
     */
    public function up(): void
    {
        Schema::table('dosen_pengajar_kelas', function (Blueprint $table) {
            $table->unique(['kelas_kuliah_id', 'dosen_id'], 'dpk_kelas_dosen_unique');
        });
    }

    public function down(): void
    {
        Schema::table('dosen_pengajar_kelas', function (Blueprint $table) {
            $table->dropUnique('dpk_kelas_dosen_unique');
        });
    }
};
