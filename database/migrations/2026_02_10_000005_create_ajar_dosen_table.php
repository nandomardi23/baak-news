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
        Schema::create('ajar_dosen', function (Blueprint $table) {
            $table->id();
            $table->string('id_aktivitas_mengajar')->unique(); // ID from NeoFeeder
            $table->string('id_semester', 20)->nullable();
            $table->string('id_registrasi_dosen')->index();
            $table->string('id_dosen')->index()->nullable();
            $table->string('id_kelas_kuliah')->index();
            $table->string('id_substansi')->nullable();
            $table->decimal('sks_substansi_total', 4, 2)->nullable();
            $table->decimal('rencana_tatap_muka', 4, 2)->nullable();
            $table->decimal('realisasi_tatap_muka', 4, 2)->nullable();
            $table->string('id_jenis_evaluasi')->nullable(); // Evaluasi type

            // Relasi optional ke tabel lokal
            // $table->foreign('id_dosen')->references('id_dosen')->on('dosen')->nullOnDelete();
            // $table->foreign('id_kelas_kuliah')->references('id_kelas_kuliah')->on('kelas_kuliah')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajar_dosen');
    }
};
