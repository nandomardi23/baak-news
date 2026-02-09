<?php

namespace App\Services;

use App\Models\RefAgama;
use App\Models\RefAlatTransportasi;
use App\Models\RefJenisTinggal;
use App\Models\RefKebutuhanKhusus;
use App\Models\RefPekerjaan;
use App\Models\RefPembiayaan;
use App\Models\RefPenghasilan;
use App\Models\RefWilayah;

class ReferenceSyncService
{
    protected NeoFeederService $neoFeeder;

    public function __construct(NeoFeederService $neoFeeder)
    {
        $this->neoFeeder = $neoFeeder;
    }

    /**
     * Sync All Small References (Non-pagination)
     */
    public function syncAllBasicReferences(): array
    {
        $results = [];
        
        $results['agama'] = $this->syncSimpleRef('GetAgama', RefAgama::class, 'id_agama', ['nama_agama']);
        $results['jenis_tinggal'] = $this->syncSimpleRef('GetJenisTinggal', RefJenisTinggal::class, 'id_jenis_tinggal', ['nama_jenis_tinggal']);
        $results['alat_transportasi'] = $this->syncSimpleRef('GetAlatTransportasi', RefAlatTransportasi::class, 'id_alat_transportasi', ['nama_alat_transportasi']);
        $results['pekerjaan'] = $this->syncSimpleRef('GetPekerjaan', RefPekerjaan::class, 'id_pekerjaan', ['nama_pekerjaan']);
        $results['penghasilan'] = $this->syncSimpleRef('GetPenghasilan', RefPenghasilan::class, 'id_penghasilan', ['nama_penghasilan']);
        $results['kebutuhan_khusus'] = $this->syncSimpleRef('GetKebutuhanKhusus', RefKebutuhanKhusus::class, 'id_kebutuhan_khusus', ['nama_kebutuhan_khusus']);
        $results['pembiayaan'] = $this->syncSimpleRef('GetPembiayaan', RefPembiayaan::class, 'id_pembiayaan', ['nama_pembiayaan']);

        return $results;
    }

    /**
     * Helper for syncing simple reference tables
     */
    private function syncSimpleRef(string $apiMethod, string $modelClass, string $primaryKey, array $fields): array
    {
        $method = 'get' . substr($apiMethod, 3); // GetAgama -> getAgama
        if (!method_exists($this->neoFeeder, $method)) {
             // Fallback if method naming varies, but we standardised it in NeoFeederService
             // But actually I used request() directly in the previous thought, let's just use request() here if needed
             // or stick to the methods I just added.
        }

        $response = $this->neoFeeder->$method();
        
        if (!$response || !isset($response['data'])) {
            return ['status' => 'failed', 'message' => 'No data'];
        }

        $count = 0;
        foreach ($response['data'] as $item) {
            $data = [];
            $data[$primaryKey] = $item[$primaryKey] ?? null;
            
            foreach ($fields as $field) {
                $data[$field] = $item[$field] ?? null;
            }

            $modelClass::updateOrCreate(
                [$primaryKey => $data[$primaryKey]],
                $data
            );
            $count++;
        }

        return ['status' => 'success', 'count' => $count];
    }

    /**
     * Sync Wilayah (Regions) - Needs pagination
     */
    public function syncWilayah(int $offset = 0, int $limit = 2000): array
    {
        // Get total count
        $countResponse = $this->neoFeeder->getCountWilayah();
        $totalAll = $countResponse['data'][0]['total'] ?? $countResponse['data']['total'] ?? 0;
        $totalAll = (int)$totalAll;

        $response = $this->neoFeeder->getWilayah($limit, $offset);
        
        if (!$response || !isset($response['data'])) {
             // Handle empty response
             return [
                'synced' => 0,
                'total_all' => $totalAll,
                'has_more' => false
             ];
        }

        $synced = 0;
        foreach ($response['data'] as $item) {
            RefWilayah::updateOrCreate(
                ['id_wilayah' => $item['id_wilayah']],
                [
                    'nama_wilayah' => $item['nama_wilayah'] ?? '',
                    'id_induk_wilayah' => $item['id_induk_wilayah'] ?? null,
                    'id_level_wilayah' => $item['id_level_wilayah'] ?? null,
                    'id_negara' => $item['id_negara'] ?? null,
                ]
            );
            $synced++;
        }

        $nextOffset = $offset + $limit;
        $hasMore = $nextOffset < $totalAll;

        return [
            'synced' => $synced,
            'total_all' => $totalAll,
            'offset' => $offset,
            'next_offset' => $hasMore ? $nextOffset : null,
            'has_more' => $hasMore,
            'progress' => ($totalAll > 0) ? round(($nextOffset / $totalAll) * 100) : 0
        ];
    }
}
