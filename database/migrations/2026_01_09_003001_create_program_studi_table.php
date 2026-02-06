<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('id_prodi')->unique()->comment('ID dari Neo Feeder');
            $table->string('kode_prodi');
            $table->string('nama_prodi');
            $table->string('jenjang'); // D3, D4, S1, Profesi
            $table->string('akreditasi')->nullable();
            $table->date('tanggal_akreditasi')->nullable();
            $table->date('tanggal_berakhir_akreditasi')->nullable();
            $table->string('sk_akreditasi')->nullable();
            $table->enum('jenis_program', ['reguler', 'rpl'])->default('reguler');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};
