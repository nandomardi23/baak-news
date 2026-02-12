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
        Schema::create('konversi_kampus_merdeka', function (Blueprint $table) {
            $table->id();
            $table->string('id_konversi_aktivitas')->unique(); // ID from NeoFeeder
            $table->string('id_matkul')->index()->nullable();
            $table->string('nama_mata_kuliah')->nullable();
            $table->string('id_anggota')->index()->nullable(); // Link to AnggotaAktivitas
            $table->string('id_aktivitas_mahasiswa')->index()->nullable();
            $table->string('judul_aktivitas_mahasiswa')->nullable();
            $table->string('id_semester')->index()->nullable();
            $table->string('nim')->index()->nullable();
            $table->string('nama_mahasiswa')->nullable();
            $table->decimal('sks_mata_kuliah', 4, 2)->nullable();
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('nilai_indeks')->nullable();
            $table->string('nilai_huruf')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konversi_kampus_merdeka');
    }
};
