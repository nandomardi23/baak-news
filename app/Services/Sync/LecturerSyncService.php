<?php

namespace App\Services\Sync;

use App\Models\Dosen;
use App\Models\AjarDosen;

class LecturerSyncService extends BaseSyncService
{
    public function syncDosen(int $offset = 0, int $limit = 500): array
    {
        // Get total count from API
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountDosen();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("SyncDosen: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getDosen($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                Dosen::updateOrCreate(
                    ['id_dosen' => $item['id_dosen']],
                    [
                        'nama' => $item['nama_dosen'],
                        'nidn' => $item['nidn'],
                        'nip' => $item['nip'],
                        'jenis_kelamin' => $item['jenis_kelamin'],
                        'id_agama' => $item['id_agama'],
                        'tanggal_lahir' => $item['tanggal_lahir'],
                        'id_status_aktif' => $item['id_status_aktif'],
                        'status_aktif' => $item['nama_status_aktif'],
                        'id_prodi' => $item['id_prodi'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Dosen {$item['nama_dosen']}: " . $e->getMessage();
                \Illuminate\Support\Facades\Log::error("Sync Dosen Error: " . $e->getMessage());
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }

    public function syncAjarDosen(int $offset = 0, int $limit = 100, ?string $idSemester = null): array
    {
        $filter = $idSemester ? "id_semester = '{$idSemester}'" : "";

        // Get total count from API
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountAktivitasMengajarDosen($filter);
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning("SyncAjarDosen: GetCount failed. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getAktivitasMengajarDosen($limit, $offset, $filter);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                if (empty($item['id_aktivitas_mengajar'])) {
                    continue;
                }

                AjarDosen::updateOrCreate(
                    ['id_aktivitas_mengajar' => $item['id_aktivitas_mengajar']],
                    [
                        'id_registrasi_dosen' => $item['id_registrasi_dosen'] ?? '',
                        'id_dosen' => $item['id_dosen'] ?? null,
                        'id_kelas_kuliah' => $item['id_kelas_kuliah'] ?? '',
                        'id_substansi' => $item['id_substansi'] ?? null,
                        'sks_substansi_total' => $item['sks_substansi_total'] ?? 0,
                        'rencana_tatap_muka' => $item['rencana_tatap_muka'] ?? 0,
                        'realisasi_tatap_muka' => $item['realisasi_tatap_muka'] ?? 0,
                        'id_jenis_evaluasi' => $item['id_jenis_evaluasi'] ?? null,
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "AjarDosen Sync Error: " . $e->getMessage();
                \Illuminate\Support\Facades\Log::error("AjarDosen Sync Error item: " . json_encode($item) . " Error: " . $e->getMessage());
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = $totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit);
        $progress = $totalAll > 0 ? min(100, round($nextOffset / $totalAll * 100)) : 100;

        return [
            'total' => $batchCount,
            'synced' => $synced,
            'errors' => $errors,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress,
        ];
    }
}
