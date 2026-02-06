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
        $query = ActivityLog::with('user')->latest();

        // Filter by action
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Filter by user
        if ($userId = $request->input('user')) {
            $query->where('user_id', $userId);
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->paginate(20)
            ->withQueryString()
            ->through(fn($log) => [
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
            ]);

        // Get unique actions for filter dropdown
        $actions = ActivityLog::distinct('action')
            ->pluck('action')
            ->filter()
            ->values();

        return Inertia::render('Admin/Logs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => [
                'action' => $request->input('action'),
                'search' => $request->input('search'),
            ],
        ]);
    }
}
