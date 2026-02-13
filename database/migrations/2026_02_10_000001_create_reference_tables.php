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
        // 1. Agama
        Schema::create('ref_agama', function (Blueprint $table) {
            $table->id();
            $table->string('id_agama')->nullable()->index(); // From NeoFeeder
            $table->string('nama_agama');
            $table->timestamps();
        });

        // 2. Jenis Tinggal
        Schema::create('ref_jenis_tinggal', function (Blueprint $table) {
            $table->id();
            $table->string('id_jenis_tinggal')->nullable()->index(); // From NeoFeeder
            $table->string('nama_jenis_tinggal');
            $table->timestamps();
        });

        // 3. Alat Transportasi
        Schema::create('ref_alat_transportasi', function (Blueprint $table) {
            $table->id();
            $table->string('id_alat_transportasi')->nullable()->index(); // From NeoFeeder
            $table->string('nama_alat_transportasi');
            $table->timestamps();
        });

        // 4. Pekerjaan
        Schema::create('ref_pekerjaan', function (Blueprint $table) {
            $table->id();
            $table->integer('id_pekerjaan')->nullable()->index(); // From NeoFeeder
            $table->string('nama_pekerjaan');
            $table->timestamps();
        });

        // 5. Penghasilan
        Schema::create('ref_penghasilan', function (Blueprint $table) {
            $table->id();
            $table->string('id_penghasilan')->nullable()->index(); // From NeoFeeder
            $table->string('nama_penghasilan');
            $table->timestamps();
        });

        // 6. Kebutuhan Khusus
        Schema::create('ref_kebutuhan_khusus', function (Blueprint $table) {
            $table->id();
            $table->string('id_kebutuhan_khusus')->nullable()->index(); // From NeoFeeder
            $table->string('nama_kebutuhan_khusus');
            $table->timestamps();
        });
        
        // 7. Pembiayaan
        Schema::create('ref_pembiayaan', function (Blueprint $table) {
            $table->id();
            $table->string('id_pembiayaan')->nullable()->index(); // From NeoFeeder
            $table->string('nama_pembiayaan');
            $table->timestamps();
        });

        // 8. Wilayah (Simplified)
        Schema::create('ref_wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('id_wilayah')->index(); // From NeoFeeder
            $table->string('nama_wilayah');
            $table->string('id_induk_wilayah')->nullable()->index();
            $table->integer('id_level_wilayah')->nullable();
            $table->string('id_negara')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_wilayah');
        Schema::dropIfExists('ref_pembiayaan');
        Schema::dropIfExists('ref_kebutuhan_khusus');
        Schema::dropIfExists('ref_penghasilan');
        Schema::dropIfExists('ref_pekerjaan');
        Schema::dropIfExists('ref_alat_transportasi');
        Schema::dropIfExists('ref_jenis_tinggal');
        Schema::dropIfExists('ref_agama');
    }
};
