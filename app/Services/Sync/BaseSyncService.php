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
}
