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
        // 1. Kurikulum
        Schema::create('kurikulum', function (Blueprint $table) {
            $table->id();
            $table->string('id_kurikulum')->unique(); // From NeoFeeder
            $table->string('nama_kurikulum');
            $table->string('id_prodi')->nullable()->index();
            $table->string('id_semester')->nullable(); // Mulai berlaku
            $table->integer('jumlah_sks_lulus')->nullable();
            $table->integer('jumlah_sks_wajib')->nullable();
            $table->integer('jumlah_sks_pilihan')->nullable();
            $table->timestamps();
        });

        // 2. Mata Kuliah Kurikulum (Pivot between Kurikulum and MataKuliah)
        Schema::create('matkul_kurikulum', function (Blueprint $table) {
            $table->id();
            $table->string('id_kurikulum')->index();
            $table->string('id_matkul')->index();
            $table->integer('semester')->nullable(); // Semester paket
            $table->integer('sks_mata_kuliah')->nullable();
            $table->integer('sks_tatap_muka')->nullable();
            $table->integer('sks_praktek')->nullable();
            $table->integer('sks_praktek_lapangan')->nullable();
            $table->integer('sks_simulasi')->nullable();
            $table->boolean('apakah_wajib')->default(false);
            $table->timestamps();

            $table->index(['id_kurikulum', 'id_matkul']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matkul_kurikulum');
        Schema::dropIfExists('kurikulum');
    }
};
