<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

trait ApiSyncResponses
{
    /**
     * Standardized Success Response
     */
    protected function successResponse(string $message, array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Standardized Error Response
     */
    protected function errorResponse(string $message, int $code = 500): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    /**
     * Generic Sync Handler to reduce duplication
     */
    protected function handleSync(Request $request, callable $callback, string $successMessage): JsonResponse
    {
        try {
            // Optimization: Prevent timeout and memory leaks during heavy sync
            set_time_limit(300); 
            DB::disableQueryLog();

            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 100); // Default limit 100 for stability
            $idSemester = $request->input('id_semester');
            $syncSince = $request->input('sync_since');

            // Execute the callback with processed parameters
            $result = $callback($offset, $limit, $idSemester, $syncSince);

            return $this->successResponse($successMessage, $result);
        } catch (\Exception $e) {
            Log::error("Sync Error: " . $e->getMessage());
            return $this->errorResponse($e->getMessage());
        }
    }
}
