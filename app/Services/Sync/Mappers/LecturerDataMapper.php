<?php

namespace App\Services\Sync\Mappers;

class LecturerDataMapper
{
    public static function mapDosen(array $data): array
    {
        $records = [];
        foreach ($data as $item) {
            $records[] = [
                'id_dosen' => $item['id_dosen'],
                'nama' => $item['nama_dosen'],
                'nidn' => $item['nidn'],
                'nip' => $item['nip'],
                'jenis_kelamin' => $item['jenis_kelamin'],
                'id_agama' => $item['id_agama'],
                'tanggal_lahir' => isset($item['tanggal_lahir']) ? date('Y-m-d', strtotime($item['tanggal_lahir'])) : null,
                'id_status_aktif' => $item['id_status_aktif'],
                'status_aktif' => $item['nama_status_aktif'],
                'id_prodi' => $item['id_prodi'] ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }

    public static function mapAjarDosen(array $data, ?string $idSemester): array
    {
        $records = [];
        foreach ($data as $item) {
            if (empty($item['id_aktivitas_mengajar'])) continue;

            $records[] = [
                'id_aktivitas_mengajar' => $item['id_aktivitas_mengajar'],
                'id_registrasi_dosen' => $item['id_registrasi_dosen'] ?? '',
                'id_dosen' => $item['id_dosen'] ?? null,
                'id_kelas_kuliah' => $item['id_kelas_kuliah'] ?? '',
                'id_substansi' => $item['id_substansi'] ?? null,
                'sks_substansi_total' => $item['sks_substansi_total'] ?? 0,
                'rencana_tatap_muka' => $item['rencana_tatap_muka'] ?? 0,
                'realisasi_tatap_muka' => $item['realisasi_tatap_muka'] ?? 0,
                'id_jenis_evaluasi' => $item['id_jenis_evaluasi'] ?? null,
                'id_semester' => $idSemester ?? ($item['id_semester'] ?? $item['id_periode'] ?? null),
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }
        return $records;
    }
}
