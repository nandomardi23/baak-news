<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Sync\CurriculumSyncService;
use App\Traits\ApiSyncResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculumSyncController extends Controller
{
    use ApiSyncResponses;

    public function syncKurikulum(Request $request, CurriculumSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Kurikulum', ['total' => $syncService->getCountKurikulum()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncKurikulum($o, $l, $ss), 'Sync Kurikulum berhasil');
    }

    public function syncMataKuliah(Request $request, CurriculumSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Mata Kuliah', ['total' => $syncService->getCountMataKuliah()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncMataKuliah($o, $l, $ss), 'Sync Mata Kuliah berhasil');
    }
}
