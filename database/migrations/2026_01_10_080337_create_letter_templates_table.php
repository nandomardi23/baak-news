<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // KRS, KHS, Transkrip, Surat Aktif, etc.
            $table->string('slug')->unique(); // krs, khs, transkrip, etc.
            $table->string('type')->default('surat'); // surat, krs, khs, transkrip
            $table->text('description')->nullable();
            $table->json('canvas_data')->nullable(); // Fabric.js canvas state
            $table->string('file_path')->nullable();
            $table->string('page_size')->default('A4'); // A4, Letter, Legal
            $table->string('orientation')->default('portrait'); // portrait, landscape
            $table->integer('width')->default(794); // A4 width in pixels at 96dpi
            $table->integer('height')->default(1123); // A4 height in pixels at 96dpi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_templates');
    }
};

