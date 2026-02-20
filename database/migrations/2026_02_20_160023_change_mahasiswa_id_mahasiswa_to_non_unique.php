<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Change id_mahasiswa from UNIQUE to INDEX to support students
     * with multiple registrations (e.g., D3 -> S1 -> Profesi Ners).
     * Each registration has a unique id_registrasi_mahasiswa but 
     * shares the same id_mahasiswa.
     */
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            // Drop unique constraint on id_mahasiswa
            $table->dropUnique(['id_mahasiswa']);
            // Add regular index instead
            $table->index('id_mahasiswa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropIndex(['id_mahasiswa']);
            $table->unique('id_mahasiswa');
        });
    }
};
