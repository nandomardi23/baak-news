<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // KRS - Kartu Rencana Studi
        Schema::create('krs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
            $table->string('id_semester')->nullable();
            $table->string('id_registrasi_mahasiswa')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'tahun_akademik_id']);
            $table->unique(['id_registrasi_mahasiswa', 'id_semester'], 'krs_reg_sem_unique');
            $table->index('mahasiswa_id');
            $table->index('tahun_akademik_id');
        });

        // KRS Detail - Mata kuliah yang diambil
        Schema::create('krs_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('krs_id')->constrained('krs')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('id_kelas_kuliah')->nullable();
            $table->string('id_matkul', 50)->nullable();
            $table->string('kode_mata_kuliah', 20)->nullable();
            $table->string('nama_mata_kuliah', 200)->nullable();
            $table->integer('sks_mata_kuliah')->default(0);
            $table->string('nama_kelas_kuliah', 100)->nullable();
            $table->string('nama_kelas')->nullable();
            $table->string('angkatan', 10)->nullable();
            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->string('nama_dosen')->nullable();
            $table->timestamps();

            $table->unique(['krs_id', 'id_matkul'], 'krs_detail_krs_matkul_unique');
            $table->index('krs_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('krs_detail');
        Schema::dropIfExists('krs');
    }
};
