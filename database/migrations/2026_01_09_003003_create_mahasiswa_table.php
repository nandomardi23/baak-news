<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('id_mahasiswa', 50)->comment('ID dari Neo Feeder');
            $table->string('nim', 50)->unique();
            $table->string('nama', 150);
            $table->string('nama_mahasiswa', 150)->nullable();

            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('id_agama', 50)->nullable();
            $table->string('nama_agama', 100)->nullable();
            $table->string('nik', 50)->nullable();
            $table->string('nisn', 50)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('kewarganegaraan')->nullable();

            // Address Info
            $table->text('jalan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('dusun', 100)->nullable();
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kota_kabupaten', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('id_wilayah', 50)->nullable();
            $table->string('nama_wilayah', 150)->nullable();
            $table->string('kode_pos', 10)->nullable();

            // Living / Transport
            $table->string('id_jenis_tinggal', 50)->nullable();
            $table->string('nama_jenis_tinggal', 100)->nullable();
            $table->string('id_alat_transportasi', 50)->nullable();
            $table->string('nama_alat_transportasi', 100)->nullable();

            // Contact
            $table->string('telepon', 50)->nullable();
            $table->string('no_hp', 50)->nullable();
            $table->string('handphone', 50)->nullable();
            $table->string('email', 100)->nullable();

            // Parent - Father
            $table->string('nik_ayah', 50)->nullable();
            $table->string('nama_ayah', 150)->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('id_pendidikan_ayah', 50)->nullable();
            $table->string('nama_pendidikan_ayah', 100)->nullable();
            $table->string('id_pekerjaan_ayah', 50)->nullable();
            $table->string('nama_pekerjaan_ayah', 100)->nullable();
            $table->string('id_penghasilan_ayah', 50)->nullable();
            $table->string('nama_penghasilan_ayah', 100)->nullable();

            // Parent - Mother
            $table->string('nik_ibu', 50)->nullable();
            $table->string('nama_ibu', 150)->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('id_pendidikan_ibu', 50)->nullable();
            $table->string('nama_pendidikan_ibu', 100)->nullable();
            $table->string('id_pekerjaan_ibu', 50)->nullable();
            $table->string('nama_pekerjaan_ibu', 100)->nullable();
            $table->string('id_penghasilan_ibu', 50)->nullable();
            $table->string('nama_penghasilan_ibu', 100)->nullable();

            // Guardian
            $table->string('nama_wali', 100)->nullable();
            $table->date('tanggal_lahir_wali')->nullable();
            $table->string('id_pendidikan_wali', 50)->nullable();
            $table->string('nama_pendidikan_wali', 100)->nullable();
            $table->string('id_pekerjaan_wali', 50)->nullable();
            $table->string('nama_pekerjaan_wali', 100)->nullable();
            $table->string('id_penghasilan_wali', 50)->nullable();
            $table->string('nama_penghasilan_wali', 100)->nullable();

            // Kebutuhan Khusus
            $table->string('id_kebutuhan_khusus_mahasiswa', 50)->nullable();
            $table->string('nama_kebutuhan_khusus_mahasiswa', 100)->nullable();
            $table->string('id_kebutuhan_khusus_ayah', 50)->nullable();
            $table->string('nama_kebutuhan_khusus_ayah', 100)->nullable();
            $table->string('id_kebutuhan_khusus_ibu', 50)->nullable();
            $table->string('nama_kebutuhan_khusus_ibu', 100)->nullable();

            // Academic Info
            $table->foreignId('program_studi_id')->nullable()->constrained('program_studi')->nullOnDelete();
            $table->string('id_prodi', 50)->nullable()->comment('ID Prodi dari Neo Feeder');
            $table->string('angkatan', 10)->nullable();
            $table->string('id_periode', 20)->nullable();
            $table->string('status_mahasiswa', 50)->nullable();
            $table->string('id_status_mahasiswa', 50)->nullable();
            $table->string('nama_status_mahasiswa', 100)->nullable();
            $table->string('id_registrasi_mahasiswa')->nullable()->comment('ID Registrasi dari Neo Feeder');
            $table->decimal('ipk', 4, 2)->nullable();
            $table->decimal('ips', 4, 2)->nullable();
            $table->integer('sks_tempuh')->nullable();
            $table->integer('sks_total')->nullable();
            $table->foreignId('dosen_wali_id')->nullable()->constrained('dosen')->nullOnDelete();

            // Graduation / Keluar
            $table->text('keterangan_keluar')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->string('id_periode_keluar', 20)->nullable();
            $table->string('nomor_sk_yudisium', 100)->nullable();
            $table->date('tanggal_sk_yudisium')->nullable();
            $table->string('nomor_ijazah', 100)->nullable();

            // Old info (kept for compatibility if needed, but made nullable)
            $table->text('alamat_ortu')->nullable();
            $table->string('rt_ortu')->nullable();
            $table->string('rw_ortu')->nullable();
            $table->string('kelurahan_ortu')->nullable();
            $table->string('kecamatan_ortu')->nullable();
            $table->string('kota_kabupaten_ortu')->nullable();
            $table->string('provinsi_ortu')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('pekerjaan_ibu')->nullable();

            $table->timestamps();

            $table->index('nim');
            $table->index('nama');
            $table->index('program_studi_id');
            $table->index('id_mahasiswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
