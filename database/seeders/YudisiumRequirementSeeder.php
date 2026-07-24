<?php

namespace Database\Seeders;

use App\Models\YudisiumRequirement;
use Illuminate\Database\Seeder;

class YudisiumRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requirements = [
            [
                'nama_syarat' => 'Bebas Pinjaman Perpustakaan',
                'deskripsi' => 'Surat keterangan bebas pinjaman buku dari UPT Perpustakaan. Wajib dilampirkan bagi seluruh mahasiswa.',
                'is_upload_required' => true,
                'is_active' => true,
            ],
            [
                'nama_syarat' => 'Bebas Tanggungan Keuangan',
                'deskripsi' => 'Surat keterangan bebas tunggakan SPP dan biaya pendidikan lainnya dari Bagian Keuangan.',
                'is_upload_required' => true,
                'is_active' => true,
            ],
            [
                'nama_syarat' => 'Bebas Tanggungan Laboratorium',
                'deskripsi' => 'Surat keterangan tidak ada tanggungan alat laboratorium.',
                'is_upload_required' => true,
                'is_active' => true,
            ],
            [
                'nama_syarat' => 'Bukti Penyerahan Skripsi/Tugas Akhir',
                'deskripsi' => 'Bukti tanda terima penyerahan laporan Skripsi / Tugas Akhir (Hardcopy & Softcopy).',
                'is_upload_required' => true,
                'is_active' => true,
            ],
            [
                'nama_syarat' => 'Sertifikat TOEFL / Bahasa Inggris',
                'deskripsi' => 'Sertifikat TOEFL dengan skor minimal yang telah ditentukan oleh program studi masing-masing.',
                'is_upload_required' => true,
                'is_active' => true,
            ],
            [
                'nama_syarat' => 'Pas Foto Ijazah',
                'deskripsi' => 'Pas foto resmi untuk Ijazah (Hitam putih / Warna sesuai ketentuan akademik).',
                'is_upload_required' => true,
                'is_active' => true,
            ],
            [
                'nama_syarat' => 'Bukti Publikasi Jurnal (Opsional/Sesuai Prodi)',
                'deskripsi' => 'Bukti publikasi jurnal ilmiah bagi program studi yang mewajibkan.',
                'is_upload_required' => false,
                'is_active' => true,
            ],
            [
                'nama_syarat' => 'Sertifikat PKKMB (Pengenalan Kampus)',
                'deskripsi' => 'Bukti telah mengikuti kegiatan orientasi mahasiswa baru (PKKMB).',
                'is_upload_required' => false,
                'is_active' => true,
            ],
        ];

        foreach ($requirements as $requirement) {
            YudisiumRequirement::firstOrCreate(
                ['nama_syarat' => $requirement['nama_syarat']],
                $requirement
            );
        }
    }
}
