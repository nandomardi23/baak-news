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
        Schema::create('bimbingan_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_bimbingan_mahasiswa')->unique(); // ID from NeoFeeder
            $table->string('id_aktivitas_mahasiswa')->index(); // Link to AktivitasMahasiswa (Thesis/Skripsi)
            $table->string('id_dosen')->index(); // Dosen Pembimbing
            $table->string('pembimbing_ke')->nullable(); // 1, 2, dst
            $table->string('id_kategori_kegiatan')->nullable(); // ID Kategori (e.g., 110401 - Membimbing Skripsi)
            
            // Relasi optional
            // $table->foreign('id_dosen')->references('id_dosen')->on('dosen')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bimbingan_mahasiswa');
    }
};
