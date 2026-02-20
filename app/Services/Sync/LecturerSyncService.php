<?php

namespace App\Services\Sync;

use App\Models\Dosen;
use App\Models\AjarDosen;

class LecturerSyncService extends BaseSyncService
{
    public function syncDosen(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
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

        $filter = $this->getFilter('', $syncSince);
        $response = $this->neoFeeder->getDosen($limit, $offset, $filter);

        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        if (!empty($data)) {
            $records = [];
            foreach ($data as $item) {
                $records[] = [
                    'id_dosen' => $item['id_dosen'],
                    'nama' => $item['nama_dosen'],
                    'nidn' => $item['nidn'],
                    'nip' => $item['nip'],
                    'jenis_kelamin' => $item['jenis_kelamin'],
                    'id_agama' => $item['id_agama'],
                    'tanggal_lahir' => $item['tanggal_lahir'],
                    'id_status_aktif' => $item['id_status_aktif'],
                    'status_aktif' => $item['nama_status_aktif'],
                    'id_prodi' => $item['id_prodi'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];
            }

            try {
                foreach (array_chunk($records, 500) as $chunk) {
                    Dosen::upsert(
                        $chunk,
                        ['id_dosen'],
                        ['nama', 'nidn', 'nip', 'jenis_kelamin', 'id_agama', 'tanggal_lahir', 'id_status_aktif', 'status_aktif', 'id_prodi', 'updated_at']
                    );
                }
                $synced = count($records);
            } catch (\Exception $e) {
                $errors[] = "Dosen Batch Error: " . $e->getMessage();
                \Illuminate\Support\Facades\Log::error("Dosen batch upsert failed", ['error' => $e->getMessage()]);
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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

    public function syncAjarDosen(int $offset = 0, int $limit = 500, ?string $idSemester = null, ?string $syncSince = null): array
    {
        $baseFilter = $idSemester ? "id_periode = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);

        // Get total count from API
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->requestQuick('GetCountAktivitasMengajarDosen', ['filter' => $filter]);
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

        if (!empty($data)) {
            $records = [];
            foreach ($data as $item) {
                if (empty($item['id_aktivitas_mengajar'])) {
                    continue;
                }

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

            if (!empty($records)) {
                try {
                    foreach (array_chunk($records, 500) as $chunk) {
                        AjarDosen::upsert(
                            $chunk,
                            ['id_aktivitas_mengajar'],
                            ['id_registrasi_dosen', 'id_dosen', 'id_kelas_kuliah', 'id_substansi', 'sks_substansi_total', 'rencana_tatap_muka', 'realisasi_tatap_muka', 'id_jenis_evaluasi', 'id_semester', 'updated_at']
                        );
                    }
                    $synced = count($records);
                } catch (\Exception $e) {
                    $errors[] = "AjarDosen Batch Error: " . $e->getMessage();
                    \Illuminate\Support\Facades\Log::error("AjarDosen batch upsert failed", ['error' => $e->getMessage()]);
                }
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($totalAll > 0 ? $nextOffset < $totalAll : ($batchCount === $limit)) && ($batchCount > 0);
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
    public function getCountDosen(): int
    {
        try {
            $response = $this->neoFeeder->getCountDosen();
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getCountAjarDosen(?string $idSemester = null, ?string $syncSince = null): int
    {
        $baseFilter = $idSemester ? "id_periode = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);
        try {
            $response = $this->neoFeeder->requestQuick('GetCountAktivitasMengajarDosen', ['filter' => $filter]);
            return $this->extractCount($response['data'] ?? []);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
