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
        Schema::create('arsip_surat', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->date('tanggal_diterima')->nullable(); // Khusus surat masuk
            $table->string('asal_surat')->nullable();     // Khusus surat masuk
            $table->string('tujuan_surat')->nullable();    // Khusus surat keluar
            $table->string('perihal');
            $table->text('keterangan')->nullable();
            $table->string('file_path');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('jenis');
            $table->index('tanggal_surat');
            $table->index('nomor_surat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_surat');
    }
};
