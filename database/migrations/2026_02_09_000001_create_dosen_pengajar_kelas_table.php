<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dosen_pengajar_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('id_aktivitas_mengajar')->nullable()->comment('UUID from NeoFeeder');

            // Foreign Keys
            $table->foreignId('kelas_kuliah_id')->constrained('kelas_kuliah')->onDelete('cascade');
            $table->string('id_kelas_kuliah')->nullable()->index(); // Helper for sync

            $table->foreignId('dosen_id')->nullable()->constrained('dosen')->onDelete('cascade');
            $table->string('id_dosen')->nullable()->index(); // Helper for sync
            $table->string('id_registrasi_dosen')->nullable();

            // Details
            $table->decimal('sks_substansi_total', 5, 2)->nullable();
            $table->integer('rencana_tatap_muka')->nullable();
            $table->integer('realisasi_tatap_muka')->nullable();
            $table->string('id_jenis_evaluasi')->nullable();
            $table->string('nama_jenis_evaluasi')->nullable();

            $table->timestamps();

            // Indexes for faster lookups
            $table->unique(['kelas_kuliah_id', 'dosen_id'], 'dpk_kelas_dosen_unique');
            $table->index(['id_kelas_kuliah', 'id_dosen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_pengajar_kelas');
    }
};
