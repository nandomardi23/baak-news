<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $this->buildIndexQuery($request);

        $logs = $query->paginate(20)
            ->withQueryString()
            ->through(fn($log) => $this->transformIndexData($log));

        return Inertia::render('Admin/Logs/Index', [
            'logs' => $logs,
            'actions' => $this->getActions(),
            'filters' => [
                'action' => $request->input('action'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    private function buildIndexQuery(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($userId = $request->input('user')) {
            $query->where('user_id', $userId);
        }

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        return $query;
    }

    private function transformIndexData(ActivityLog $log): array
    {
        return [
            'id' => $log->id,
            'user' => $log->user?->name ?? 'System',
            'action' => $log->action,
            'action_badge' => $log->action_badge,
            'description' => $log->description,
            'model_type' => $log->model_type ? class_basename($log->model_type) : null,
            'model_id' => $log->model_id,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at->format('d M Y H:i:s'),
            'time_ago' => $log->created_at->diffForHumans(),
        ];
    }

    private function getActions()
    {
        return ActivityLog::distinct('action')
            ->pluck('action')
            ->filter()
            ->values();
    }
}
