<?php

namespace App\Services\Sync;

use App\Models\RefAgama;
use App\Models\RefWilayah;
use App\Models\ProgramStudi;

use App\Models\RefJenisTinggal;
use App\Models\RefAlatTransportasi;
use App\Models\RefPekerjaan;
use App\Models\RefPenghasilan;
use App\Models\RefKebutuhanKhusus;
use App\Models\RefPembiayaan;

class ReferenceSyncService extends BaseSyncService
{
    /**
     * Sync Program Studi with pagination
     * Uses GetCount first, then fetches data with progress
     * 
     * @return array
     */
    public function syncProdi(int $offset = 0, int $limit = 100): array
    {
        // Get total count from API
        // Get total count from API
        $totalAll = 0;
        try {
            $countResponse = $this->neoFeeder->getCountProdi();
            if ($countResponse && isset($countResponse['data'])) {
                $totalAll = $this->extractCount($countResponse['data']);
            }
        } catch (\Exception $e) {
            // Context: Log warning but continue sync with manual paging
            \Illuminate\Support\Facades\Log::warning("SyncProdi: GetCount failed, relying on pagination end. Error: " . $e->getMessage());
        }

        $response = $this->neoFeeder->getProdi($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                ProgramStudi::updateOrCreate(
                    ['id_prodi' => $item['id_prodi']],
                    [
                        'kode_program_studi' => $item['kode_program_studi'],
                        'nama_program_studi' => $item['nama_program_studi'],
                        'status' => $item['status'],
                        'id_jenjang_pendidikan' => $item['id_jenjang_pendidikan'],
                        'nama_jenjang_pendidikan' => $item['nama_jenjang_pendidikan'],
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "Prodi {$item['nama_program_studi']}: " . $e->getMessage();
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

    /**
     * Sync Semester with pagination
     * Uses GetCount first, then fetches data with progress
     * 
     * @return array
     */
    public function syncSemester(int $offset = 0, int $limit = 100): array
    {
        // Removed GetCountSemester as it is not supported
        $totalAll = 0;

        $response = $this->neoFeeder->getSemester($limit, $offset);
        
        if (!$response) {
            throw new \Exception('Gagal menghubungi Neo Feeder API');
        }

        $data = $response['data'] ?? [];
        $batchCount = count($data);
        $synced = 0;
        $errors = [];

        foreach ($data as $item) {
            try {
                \App\Models\TahunAkademik::updateOrCreate(
                    ['id_semester' => $item['id_semester']],
                    [
                        'nama_semester' => $item['nama_semester'],
                        'tahun' => $item['id_tahun_ajaran'], // Mapping id_tahun_ajaran to tahun
                        'semester' => $item['semester'] == 1 ? 'ganjil' : 'genap', // Adjust based on data
                        'tanggal_mulai' => $item['tanggal_mulai'],
                        'tanggal_selesai' => $item['tanggal_selesai'],
                        'is_active' => $item['a_periode_aktif'] == '1',
                    ]
                );
                $synced++;
            } catch (\Exception $e) {
                // Specific catch to allow continuing
                $errors[] = "Semester {$item['nama_semester']}: " . $e->getMessage();
            }
        }

        $nextOffset = $offset + $batchCount;
        $hasMore = ($batchCount === $limit);
        $progress = 0; // Unknown progress

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

    public function syncAgama(): array
    {
        try {
            $response = $this->neoFeeder->getAgama();
            $synced = 0;
            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    RefAgama::updateOrCreate(
                        ['id_agama' => $item['id_agama']],
                        ['nama_agama' => $item['nama_agama']]
                    );
                    $synced++;
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncAgama failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncWilayah(int $offset = 0, int $limit = 1000): array
    {
        $totalAll = 0;
        
        // Removed GetCountWilayah as it's not supported
        
        $response = $this->neoFeeder->getWilayah($limit, $offset);
        $synced = 0;
        $batchCount = 0;

        if ($response && isset($response['data'])) {
            $data = $response['data'];
            $batchCount = count($data);
            foreach ($data as $item) {
                RefWilayah::updateOrCreate(
                    ['id_wilayah' => $item['id_wilayah']],
                    [
                        'id_negara' => $item['id_negara'],
                        'nama_wilayah' => $item['nama_wilayah'],
                    ]
                );
                $synced++;
            }
        }
        
        $nextOffset = $offset + $batchCount;
        // Assume more if batch is full
        $hasMore = ($batchCount === $limit);
        // Progress is hard to calculate without total, so we default to 0 or remove it
        $progress = 0; 
        
        return [
            'synced' => $synced,
            'total' => $batchCount,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => $progress
        ];
    }

    public function syncJenisTinggal(): array
    {
        try {
            $response = $this->neoFeeder->getJenisTinggal();
            $synced = 0;
            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    RefJenisTinggal::updateOrCreate(
                        ['id_jenis_tinggal' => $item['id_jenis_tinggal']],
                        ['nama_jenis_tinggal' => $item['nama_jenis_tinggal']]
                    );
                    $synced++;
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncJenisTinggal failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncAlatTransportasi(): array
    {
        try {
            $response = $this->neoFeeder->getAlatTransportasi();
            $synced = 0;
            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    RefAlatTransportasi::updateOrCreate(
                        ['id_alat_transportasi' => $item['id_alat_transportasi']],
                        ['nama_alat_transportasi' => $item['nama_alat_transportasi']]
                    );
                    $synced++;
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncAlatTransportasi failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncPekerjaan(): array
    {
        try {
            $response = $this->neoFeeder->getPekerjaan();
            $synced = 0;
            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    RefPekerjaan::updateOrCreate(
                        ['id_pekerjaan' => $item['id_pekerjaan']],
                        ['nama_pekerjaan' => $item['nama_pekerjaan']]
                    );
                    $synced++;
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncPekerjaan failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncPenghasilan(): array
    {
        try {
            $response = $this->neoFeeder->getPenghasilan();
            $synced = 0;
            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    RefPenghasilan::updateOrCreate(
                        ['id_penghasilan' => $item['id_penghasilan']],
                        ['nama_penghasilan' => $item['nama_penghasilan']]
                    );
                    $synced++;
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncPenghasilan failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncKebutuhanKhusus(): array
    {
        try {
            $response = $this->neoFeeder->getKebutuhanKhusus();
            $synced = 0;
            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    RefKebutuhanKhusus::updateOrCreate(
                        ['id_kebutuhan_khusus' => $item['id_kebutuhan_khusus']],
                        ['nama_kebutuhan_khusus' => $item['nama_kebutuhan_khusus']]
                    );
                    $synced++;
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncKebutuhanKhusus failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function syncPembiayaan(): array
    {
        try {
            $response = $this->neoFeeder->getPembiayaan();
            $synced = 0;
            if ($response && isset($response['data'])) {
                foreach ($response['data'] as $item) {
                    RefPembiayaan::updateOrCreate(
                        ['id_pembiayaan' => $item['id_pembiayaan']],
                        ['nama_pembiayaan' => $item['nama_pembiayaan']]
                    );
                    $synced++;
                }
            }
            return [
                'synced' => $synced,
                'total' => $synced,
                'total_all' => $synced,
                'progress' => 100,
                'has_more' => false
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("SyncPembiayaan failed: " . $e->getMessage());
            return [
                'synced' => 0,
                'total' => 0,
                'total_all' => 0,
                'progress' => 0,
                'has_more' => false,
                'errors' => [$e->getMessage()]
            ];
        }
    }
}
