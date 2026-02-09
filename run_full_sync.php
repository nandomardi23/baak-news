<?php

use App\Services\NeoFeederSyncService;
use Illuminate\Support\Facades\Log;

$service = app(NeoFeederSyncService::class);

function runSync($service, $method, $label, $limit = 50) {
    echo "Starting Sync: $label...\n";
    $offset = 0;
    $hasMore = true;
    $totalSynced = 0;
    
    while ($hasMore) {
        try {
            echo "  Processing offset $offset... ";
            $result = $service->$method($offset, $limit);
            
            $batchSynced = $result['synced'] ?? 0;
            $totalSynced += $batchSynced;
            $hasMore = $result['has_more'] ?? false;
            $offset = $result['next_offset'] ?? ($offset + $limit);
            
            echo "Done (Synced: $batchSynced, Total: $totalSynced)\n";
            
            if (!empty($result['errors'])) {
                echo "    Errors: " . count($result['errors']) . " (Check logs)\n";
            }
            
            // Small pause to be nice to the API
            usleep(100000); // 100ms
            
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            // Retry once after delay? Or just break?
            // For now break to avoid infinite loops on error
            echo "Aborting $label sync due to error.\n";
            break;
        }
    }
    echo "Finished $label. Total Synced: $totalSynced\n\n";
}

// 1. Sync KRS
runSync($service, 'syncKrs', 'KRS', 100);

// 2. Sync Nilai
runSync($service, 'syncNilai', 'Nilai', 100);

// 3. Sync Aktivitas Kuliah
runSync($service, 'syncAktivitasKuliah', 'Aktivitas Kuliah', 100);

echo "All Syncs Completed!\n";
