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
        // 1. KRS table - Ensure unique constraint for upsert
        Schema::table('krs', function (Blueprint $table) {
            // Drop unique if exists (might be different name)
            try {
                $table->dropUnique(['mahasiswa_id', 'tahun_akademik_id']);
            } catch (\Exception $e) {}
            
            $table->unique(['id_registrasi_mahasiswa', 'id_semester']);
        });

        // 2. KRS Detail table - Add id_matkul for identification and unique constraint
        Schema::table('krs_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('krs_detail', 'id_matkul')) {
                $table->string('id_matkul', 50)->after('id_kelas_kuliah')->nullable();
            }
            if (!Schema::hasColumn('krs_detail', 'kode_mata_kuliah')) {
                $table->string('kode_mata_kuliah', 20)->after('id_matkul')->nullable();
            }
            if (!Schema::hasColumn('krs_detail', 'nama_mata_kuliah')) {
                $table->string('nama_mata_kuliah', 200)->after('kode_mata_kuliah')->nullable();
            }
            if (!Schema::hasColumn('krs_detail', 'sks_mata_kuliah')) {
                $table->integer('sks_mata_kuliah')->after('nama_mata_kuliah')->default(0);
            }
            if (!Schema::hasColumn('krs_detail', 'nama_kelas_kuliah')) {
                $table->string('nama_kelas_kuliah', 100)->after('sks_mata_kuliah')->nullable();
            }
            if (!Schema::hasColumn('krs_detail', 'angkatan')) {
                $table->string('angkatan', 10)->after('nama_kelas_kuliah')->nullable();
            }

            try {
                $table->unique(['krs_id', 'id_kelas_kuliah', 'id_matkul'], 'krs_detail_sync_unique');
            } catch (\Exception $e) {}
        });

        // 3. Nilai table - Add columns to match NeoFeeder and unique constraint
        Schema::table('nilai', function (Blueprint $table) {
            if (!Schema::hasColumn('nilai', 'id_registrasi_mahasiswa')) {
                $table->string('id_registrasi_mahasiswa', 50)->after('mahasiswa_id')->nullable();
            }
            if (!Schema::hasColumn('nilai', 'id_matkul')) {
                $table->string('id_matkul', 50)->after('mata_kuliah_id')->nullable();
            }
            if (!Schema::hasColumn('nilai', 'nama_mata_kuliah')) {
                $table->string('nama_mata_kuliah', 200)->after('id_matkul')->nullable();
            }
            if (!Schema::hasColumn('nilai', 'sks_mata_kuliah')) {
                $table->integer('sks_mata_kuliah')->after('nama_mata_kuliah')->default(0);
            }
            if (!Schema::hasColumn('nilai', 'id_periode')) {
                $table->string('id_periode', 20)->after('id_semester')->nullable();
            }
            
            // Drop unique if exists and replace with sync-friendly version
            try { $table->dropUnique('nilai_unique'); } catch (\Exception $e) {}
            
            try {
                $table->unique(['id_registrasi_mahasiswa', 'id_kelas_kuliah', 'id_matkul'], 'nilai_sync_unique');
            } catch (\Exception $e) {}
        });

        // 4. Ajar Dosen - Already unique in migration but ensure it
        Schema::table('ajar_dosen', function (Blueprint $table) {
            if (!Schema::hasColumn('ajar_dosen', 'id_semester')) {
                $table->string('id_semester', 20)->after('id_aktivitas_mengajar')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Not implemented for speed
    }
};
