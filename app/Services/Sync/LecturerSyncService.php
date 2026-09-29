<?php

namespace App\Services\Sync;

use App\Models\Dosen;
use App\Models\AjarDosen;
use App\Services\Sync\Mappers\LecturerDataMapper;

class LecturerSyncService extends BaseSyncService
{
    public function syncDosen(int $offset = 0, int $limit = 500, ?string $syncSince = null): array
    {
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
            $records = LecturerDataMapper::mapDosen($data);
            $synced = $this->upsertDosen($records, $errors);
        }

        return $this->buildPaginatedResult($batchCount, $synced, $errors, $totalAll, $offset, $limit);
    }


    private function upsertDosen(array $records, array &$errors): int
    {
        try {
            foreach (array_chunk($records, 500) as $chunk) {
                Dosen::upsert(
                    $chunk,
                    ['id_dosen'],
                    ['nama', 'nidn', 'nip', 'jenis_kelamin', 'id_agama', 'tanggal_lahir', 'id_status_aktif', 'status_aktif', 'id_prodi', 'updated_at']
                );
            }
            return count($records);
        } catch (\Exception $e) {
            $errors[] = "Dosen Batch Error: " . $e->getMessage();
            \Illuminate\Support\Facades\Log::error("Dosen batch upsert failed", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    public function syncAjarDosen(int $offset = 0, int $limit = 500, ?string $idSemester = null, ?string $syncSince = null): array
    {
        $baseFilter = $idSemester ? "id_periode = '{$idSemester}'" : "";
        $filter = $this->getFilter($baseFilter, $syncSince);

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
            $records = LecturerDataMapper::mapAjarDosen($data, $idSemester);
            if (!empty($records)) {
                $synced = $this->upsertAjarDosen($records, $errors);
            }
        }

        return $this->buildPaginatedResult($batchCount, $synced, $errors, $totalAll, $offset, $limit);
    }


    private function upsertAjarDosen(array $records, array &$errors): int
    {
        try {
            foreach (array_chunk($records, 500) as $chunk) {
                AjarDosen::upsert(
                    $chunk,
                    ['id_aktivitas_mengajar'],
                    ['id_registrasi_dosen', 'id_dosen', 'id_kelas_kuliah', 'id_substansi', 'sks_substansi_total', 'rencana_tatap_muka', 'realisasi_tatap_muka', 'id_jenis_evaluasi', 'id_semester', 'updated_at']
                );
            }
            return count($records);
        } catch (\Exception $e) {
            $errors[] = "AjarDosen Batch Error: " . $e->getMessage();
            \Illuminate\Support\Facades\Log::error("AjarDosen batch upsert failed", ['error' => $e->getMessage()]);
            return 0;
        }
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
