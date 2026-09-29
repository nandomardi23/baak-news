<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Sync\ReferenceSyncService;
use App\Traits\ApiSyncResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferenceSyncController extends Controller
{
    use ApiSyncResponses;

    public function syncReferensi(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        try {
            $type = $request->input('type');
            $subType = $request->input('sub_type');
            
            if ($type === 'wilayah') {
                return $this->handleWilayahSync($request, $syncService);
            }

            if ($subType) {
                return $this->handleSubTypeSync($request, $syncService, $subType);
            }

            return $this->handleAllSimpleSyncs($request, $syncService);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function handleWilayahSync(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
             return $this->successResponse('Count Wilayah', ['total' => $syncService->getCountWilayah()]);
        }
        return $this->handleSync($request, function($offset, $limit, $idSemester, $syncSince) use ($syncService) {
            return $syncService->syncWilayah($offset, $limit, $syncSince);
        }, 'Sync Wilayah berhasil');
    }

    private function handleSubTypeSync(Request $request, ReferenceSyncService $syncService, string $subType): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Referensi', ['total' => 100]); // Dummy > 0 to start sync
        }

        $methodName = 'sync' . \Illuminate\Support\Str::studly($subType);
        if (method_exists($syncService, $methodName)) {
            return $this->handleSync($request, function($offset, $limit, $idSemester, $syncSince) use ($syncService, $methodName) {
                return $syncService->$methodName($syncSince);
            }, "Sync Referensi $subType berhasil");
        }
        return $this->errorResponse("Sub-tipe referensi $subType tidak ditemukan", 400);
    }

    private function handleAllSimpleSyncs(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Referensi', ['total' => 100]);
        }

        $synced = 0;
        $simpleSyncs = ['Agama', 'JenisTinggal', 'AlatTransportasi', 'Pekerjaan', 'Penghasilan', 'KebutuhanKhusus', 'Pembiayaan'];
        
        foreach ($simpleSyncs as $sync) {
            $method = 'sync' . $sync;
            $res = $syncService->$method();
            $synced += $res['synced'] ?? 0;
        }

        return $this->successResponse('Sync Referensi berhasil', [
            'synced' => $synced,
            'total' => $synced,
            'total_all' => $synced,
        ]);
    }

    public function syncProdi(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Prodi', ['total' => $syncService->getCountProdi()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncProdi($o, $l, $ss), 'Sync Prodi berhasil');
    }

    public function syncSemester(Request $request, ReferenceSyncService $syncService): JsonResponse
    {
        if ($request->boolean('only_count')) {
            return $this->successResponse('Count Semester', ['total' => $syncService->getCountSemester()]);
        }
        return $this->handleSync($request, fn($o, $l, $s, $ss) => $syncService->syncSemester($o, $l, $ss), 'Sync Semester berhasil');
    }
}
