<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nilai Mahasiswa (untuk KHS dan Transkrip)
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
            $table->string('id_semester')->nullable();
            $table->string('id_kelas_kuliah')->nullable();
            $table->decimal('nilai_angka', 5, 2)->nullable();
            $table->string('nilai_huruf', 2)->nullable(); // A, AB, B, BC, C, D, E
            $table->decimal('nilai_indeks', 3, 2)->nullable(); // 4.00, 3.50, etc
            $table->timestamps();
            
            $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'tahun_akademik_id'], 'nilai_unique');
            $table->index('mahasiswa_id');
            $table->index('tahun_akademik_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};
