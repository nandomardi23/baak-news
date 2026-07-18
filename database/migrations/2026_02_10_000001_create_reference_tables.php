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
        // Unified references table (replaces ref_agama, ref_jenis_tinggal,
        // ref_alat_transportasi, ref_pekerjaan, ref_penghasilan,
        // ref_kebutuhan_khusus, ref_pembiayaan)
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();    // agama, jenis_tinggal, alat_transportasi, etc.
            $table->string('external_id')->index();  // ID from NeoFeeder
            $table->string('nama');                  // Display name
            $table->timestamps();

            $table->unique(['type', 'external_id']); // Prevent duplicates per type
        });

        // Wilayah kept separate — has unique columns (hierarchy, country)
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
        Schema::dropIfExists('references');
    }
};
