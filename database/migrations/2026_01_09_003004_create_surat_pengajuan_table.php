<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->enum('jenis_surat', ['aktif_kuliah', 'krs', 'khs', 'transkrip']);
            $table->string('keperluan')->nullable(); // Keperluan surat (beasiswa, magang, dll)
            $table->json('data_tambahan')->nullable(); // Data tambahan yang diisi mahasiswa
            $table->enum('status', ['pending', 'approved', 'rejected', 'printed'])->default('pending');
            $table->text('catatan')->nullable(); // Catatan dari staff
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index('mahasiswa_id');
            $table->index('jenis_surat');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_pengajuan');
    }
};
