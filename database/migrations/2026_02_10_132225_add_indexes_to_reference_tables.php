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
        Schema::table('ref_agama', function (Blueprint $table) {
            $table->index('id_agama');
        });
        Schema::table('ref_jenis_tinggal', function (Blueprint $table) {
            $table->index('id_jenis_tinggal');
        });
        Schema::table('ref_alat_transportasi', function (Blueprint $table) {
            $table->index('id_alat_transportasi');
        });
        Schema::table('ref_pekerjaan', function (Blueprint $table) {
            $table->index('id_pekerjaan');
        });
        Schema::table('ref_penghasilan', function (Blueprint $table) {
            $table->index('id_penghasilan');
        });
        Schema::table('ref_kebutuhan_khusus', function (Blueprint $table) {
            $table->index('id_kebutuhan_khusus');
        });
        Schema::table('ref_pembiayaan', function (Blueprint $table) {
            $table->index('id_pembiayaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ref_agama', function (Blueprint $table) { $table->dropIndex(['id_agama']); });
        Schema::table('ref_jenis_tinggal', function (Blueprint $table) { $table->dropIndex(['id_jenis_tinggal']); });
        Schema::table('ref_alat_transportasi', function (Blueprint $table) { $table->dropIndex(['id_alat_transportasi']); });
        Schema::table('ref_pekerjaan', function (Blueprint $table) { $table->dropIndex(['id_pekerjaan']); });
        Schema::table('ref_penghasilan', function (Blueprint $table) { $table->dropIndex(['id_penghasilan']); });
        Schema::table('ref_kebutuhan_khusus', function (Blueprint $table) { $table->dropIndex(['id_kebutuhan_khusus']); });
        Schema::table('ref_pembiayaan', function (Blueprint $table) { $table->dropIndex(['id_pembiayaan']); });
    }
};
