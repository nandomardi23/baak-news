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
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            $table->string('nomor_surat')->nullable()->after('id');
            $table->foreignId('pejabat_id')->nullable()->after('mahasiswa_id')->constrained('pejabat')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            $table->dropForeign(['pejabat_id']);
            $table->dropColumn(['nomor_surat', 'pejabat_id']);
        });
    }
};
