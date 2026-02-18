<?php

namespace App\Services\Sync;

use App\Services\NeoFeederService;

abstract class BaseSyncService
{
    protected NeoFeederService $neoFeeder;

    public function __construct(NeoFeederService $neoFeeder)
    {
        $this->neoFeeder = $neoFeeder;
    }

    /**
     * Helper to extract count from various API response formats
     */
    protected function extractCount($data): int
    {
        // Debugging: Log the input data to understand why count is 0
        // \Illuminate\Support\Facades\Log::info("ExtractCount Input: " . json_encode($data));

        if (is_numeric($data)) {
            return (int) $data;
        }

        if (is_array($data)) {
            if (isset($data['count'])) {
                return (int) $data['count'];
            }
            if (isset($data[0])) {
                 if (is_numeric($data[0])) {
                    return (int) $data[0];
                 }
                 if (is_array($data[0]) && isset($data[0]['count'])) {
                    return (int) $data[0]['count'];
                 }
            }
        }
        
        // Try direct key access if single object
        if (is_object($data) && isset($data->count)) {
            return (int) $data->count;
        }

        return 0;
    }

    /**
     * Perform bulk upsert in smaller chunks to avoid memory/SQL issues
     */
    protected function batchUpsert(string $modelClass, array $records, array $uniqueBy, array $updateColumns, int $chunkSize = 500): void
    {
        if (empty($records)) return;

        $chunks = array_chunk($records, $chunkSize);
        foreach ($chunks as $chunk) {
            $modelClass::upsert($chunk, $uniqueBy, $updateColumns);
        }
    }


    /**
     * Helper to construct filter string with sync_since
     */
    protected function getFilter(string $baseFilter, ?string $syncSince): string
    {
        if (empty($syncSince)) {
            return $baseFilter;
        }

        try {
            // Convert to Y-m-d (Standard SQL format)
            $date = \Carbon\Carbon::parse($syncSince)->format('Y-m-d');
            $dateFilter = "last_update >= '$date'";

            if (empty($baseFilter)) {
                return $dateFilter;
            }

            return "$baseFilter AND $dateFilter";
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("BaseSyncService: Invalid sync_since date format: $syncSince");
            return $baseFilter;
        }
    }
}
