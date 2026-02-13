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
        Schema::create('aktivitas_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('id_registrasi_mahasiswa', 100);
            $table->string('id_semester', 10);
            $table->string('nim', 20)->nullable();
            $table->string('nama_mahasiswa', 200)->nullable();
            $table->string('id_status_mahasiswa', 5)->nullable();
            $table->decimal('ips', 4, 2)->default(0);
            $table->decimal('ipk', 4, 2)->default(0);
            $table->integer('sks_semester')->default(0);
            $table->integer('sks_total')->default(0);
            $table->decimal('biaya_kuliah_smt', 15, 2)->default(0);
            $table->timestamps();

            // Composite unique key: satu record per mahasiswa per semester
            $table->unique(['id_registrasi_mahasiswa', 'id_semester'], 'akm_reg_semester_unique');
            
            // Index untuk query performa
            $table->index('nim');
            $table->index('id_semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_kuliah');
    }
};
