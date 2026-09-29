<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Sync\LecturerSyncService;
use App\Traits\ApiSyncResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LecturerSyncController extends Controller
{
    use ApiSyncResponses;

    public function syncDosen(Request $request, LecturerSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Dosen', ['total' => $syncService->getCountDosen()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncDosen($o, $l, $ss), 'Sync Dosen berhasil');
    }

    public function syncAjarDosen(Request $request, LecturerSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Ajar Dosen', ['total' => $syncService->getCountAjarDosen($request->input('id_semester'), $request->input('sync_since'))]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncAjarDosen($o, $l, $ss), 'Sync Ajar Dosen berhasil');
    }
}
