<?php

namespace App\Services\Sync\Mappers;

class ReferenceDataMapper
{
    public static function mapProdi(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_prodi' => $item['id_prodi'],
                'kode_prodi' => $item['kode_program_studi'],
                'nama_prodi' => $item['nama_program_studi'],
                'jenjang' => $item['nama_jenjang_pendidikan'],
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }

    public static function mapSemester(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_semester' => $item['id_semester'],
                'nama_semester' => $item['nama_semester'],
                'tahun' => $item['id_tahun_ajaran'],
                'semester' => $item['semester'] == 1 ? 'ganjil' : 'genap',
                'tanggal_mulai' => isset($item['tanggal_mulai']) ? date('Y-m-d', strtotime($item['tanggal_mulai'])) : null,
                'tanggal_selesai' => isset($item['tanggal_selesai']) ? date('Y-m-d', strtotime($item['tanggal_selesai'])) : null,
                'is_active' => $item['a_periode_aktif'] == '1',
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }

    public static function mapWilayah(array $data): array
    {
        $records = [];
        $now = now();
        foreach ($data as $item) {
            $records[] = [
                'id_wilayah' => $item['id_wilayah'],
                'id_negara' => $item['id_negara'],
                'nama_wilayah' => $item['nama_wilayah'],
                'id_induk_wilayah' => $item['id_induk_wilayah'],
                'id_level_wilayah' => (int) $item['id_level_wilayah'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        return $records;
    }
}
