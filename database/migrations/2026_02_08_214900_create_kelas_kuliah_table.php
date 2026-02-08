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
        Schema::create('kelas_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('id_kelas_kuliah')->unique();
            $table->string('id_matkul')->nullable();
            $table->foreignId('mata_kuliah_id')->nullable()->constrained('mata_kuliah')->nullOnDelete();
            $table->string('id_prodi')->nullable();
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('id_semester')->nullable();
            $table->foreignId('tahun_akademik_id')->nullable()->constrained('tahun_akademik')->nullOnDelete();
            $table->string('nama_kelas_kuliah')->nullable();
            $table->string('kode_mata_kuliah')->nullable();
            $table->string('nama_mata_kuliah')->nullable();
            $table->integer('sks')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->timestamps();
            
            $table->index(['id_semester', 'id_prodi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_kuliah');
    }
};
