<?php

namespace Database\Seeders;

use App\Models\YudisiumRequirement;
use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class YudisiumRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodiIds = ProgramStudi::pluck('id')->toArray();
        $prodiA = $prodiIds[0] ?? null; // ID Prodi pertama (jika ada)
        $prodiB = $prodiIds[1] ?? ($prodiIds[0] ?? null); // ID Prodi kedua (jika ada)

        $requirements = [
            [
                'nama_syarat' => 'Bebas Pinjaman Perpustakaan',
                'deskripsi' => 'Surat keterangan bebas pinjaman buku dari UPT Perpustakaan. Wajib dilampirkan bagi seluruh mahasiswa.',
                'is_upload_required' => true,
                'is_active' => true,
                'program_studi_id' => null,
            ],
            [
                'nama_syarat' => 'Bebas Tanggungan Keuangan',
                'deskripsi' => 'Surat keterangan bebas tunggakan SPP dan biaya pendidikan lainnya dari Bagian Keuangan.',
                'is_upload_required' => true,
                'is_active' => true,
                'program_studi_id' => null,
            ],
            [
                'nama_syarat' => 'Bukti Penyerahan Skripsi/Tugas Akhir',
                'deskripsi' => 'Bukti tanda terima penyerahan laporan Skripsi / Tugas Akhir (Hardcopy & Softcopy).',
                'is_upload_required' => true,
                'is_active' => true,
                'program_studi_id' => null,
            ],
            [
                'nama_syarat' => 'Sertifikat TOEFL / Bahasa Inggris',
                'deskripsi' => 'Sertifikat TOEFL dengan skor minimal yang telah ditentukan.',
                'is_upload_required' => true,
                'is_active' => true,
                'program_studi_id' => null,
            ],
        ];

        // Tambah syarat khusus jika ada prodi
        if ($prodiA) {
            $requirements[] = [
                'nama_syarat' => 'Log Book Praktik Klinik',
                'deskripsi' => 'Bukti penyelesaian jam praktik klinik (Khusus Prodi tertentu).',
                'is_upload_required' => true,
                'is_active' => true,
                'program_studi_id' => $prodiA,
            ];
        }

        if ($prodiB) {
            $requirements[] = [
                'nama_syarat' => 'Bukti Publikasi Jurnal Nasional',
                'deskripsi' => 'Bukti submit atau accepted jurnal nasional (Khusus Prodi tertentu).',
                'is_upload_required' => true,
                'is_active' => true,
                'program_studi_id' => $prodiB,
            ];
        }

        foreach ($requirements as $requirement) {
            YudisiumRequirement::firstOrCreate(
                [
                    'nama_syarat' => $requirement['nama_syarat'], 
                    'program_studi_id' => $requirement['program_studi_id']
                ],
                $requirement
            );
        }
    }
}
