<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Nilai Mahasiswa (untuk KHS dan Transkrip)
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->string('id_registrasi_mahasiswa', 50)->nullable();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('id_matkul', 50)->nullable();
            $table->string('nama_mata_kuliah', 200)->nullable();
            $table->integer('sks_mata_kuliah')->default(0);
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
            $table->string('id_semester')->nullable();
            $table->string('id_periode', 20)->nullable();
            $table->string('id_kelas_kuliah')->nullable();
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('nilai_huruf', 2)->nullable();
            $table->decimal('nilai_indeks', 3, 2)->nullable();
            $table->timestamps();

            $table->unique(['id_registrasi_mahasiswa', 'id_kelas_kuliah', 'id_matkul'], 'nilai_sync_unique');
            $table->index('mahasiswa_id');
            $table->index('tahun_akademik_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};
