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
        Schema::create('uji_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_uji_mahasiswa')->unique(); // ID from NeoFeeder
            $table->string('id_aktivitas_mahasiswa')->index(); // Link to AktivitasMahasiswa
            $table->string('id_dosen')->index(); // Dosen Penguji
            $table->string('penguji_ke')->nullable(); // 1, 2, dst
            $table->string('id_kategori_kegiatan')->nullable(); // ID Kategori (e.g., Menguji Skripsi)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uji_mahasiswa');
    }
};
