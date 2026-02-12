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
        if (is_numeric($data)) {
            return (int) $data;
        }

        if (is_array($data)) {
            if (isset($data['count'])) {
                return (int) $data['count'];
            }
            if (isset($data[0]) && is_numeric($data[0])) {
                return (int) $data[0];
            }
            // Sometimes it returns [{ "count": 123 }]
            if (isset($data[0]['count'])) {
                return (int) $data[0]['count'];
            }
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
}
