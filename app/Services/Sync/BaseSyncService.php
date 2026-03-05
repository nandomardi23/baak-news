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
        if (empty($records))
            return;

        $chunks = array_chunk($records, $chunkSize);
        foreach ($chunks as $chunk) {
            $modelClass::upsert($chunk, $uniqueBy, $updateColumns);
        }
    }


    /**
     * Helper to construct filter string with sync_since
     * If syncSince is 'AUTO' and a Model class is provided, it fetches the latest updated_at from that model.
     * Otherwise, it uses the provided date string.
     */
    protected function getFilter(string $baseFilter, ?string $syncSince, ?string $modelClass = null): string
    {
        if (empty($syncSince)) {
            return $baseFilter;
        }

        try {
            if ($syncSince === 'AUTO' && $modelClass) {
                // Fetch the latest updated_at from the database
                $latestRecord = $modelClass::orderBy('updated_at', 'desc')->first();
                if ($latestRecord && $latestRecord->updated_at) {
                    // Backdate by 1 day to be safe and ensure we don't miss records updated on the same day after the last sync
                    $date = \Carbon\Carbon::parse($latestRecord->updated_at)->subDay()->format('Y-m-d');
                } else {
                    // No records found, do a full sync
                    return $baseFilter;
                }
            } elseif ($syncSince !== 'AUTO') {
                // Convert specific date string to Y-m-d (Standard SQL format)
                $date = \Carbon\Carbon::parse($syncSince)->format('Y-m-d');
            } else {
                // AUTO was sent but no model provided, fallback to full sync
                return $baseFilter;
            }

            $dateFilter = "last_update >= '$date'";

            if (empty($baseFilter)) {
                return $dateFilter;
            }

            return "$baseFilter AND $dateFilter";
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("BaseSyncService: Error determining sync_since date: " . $e->getMessage());
            return $baseFilter;
        }
    }


    /**
     * Helper to parse dates from NeoFeeder (often d-m-Y) to MySQL (Y-m-d)
     */
    protected function parseDate($dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Try standard NeoFeeder format first
            return \Carbon\Carbon::createFromFormat('d-m-Y', $dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                // Try fallback format
                return \Carbon\Carbon::parse($dateString)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
}
