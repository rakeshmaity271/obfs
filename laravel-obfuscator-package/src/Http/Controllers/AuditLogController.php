<?php

namespace LaravelObfuscator\LaravelObfuscator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use LaravelObfuscator\LaravelObfuscator\Models\AuditLog;
use LaravelObfuscator\LaravelObfuscator\Models\User;
use LaravelObfuscator\LaravelObfuscator\Models\Project;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): JsonResponse
    {
        $logs = AuditLog::with(['user', 'project'])
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->project_id, function ($query, $projectId) {
                $query->where('project_id', $projectId);
            })
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->resource_type, function ($query, $resourceType) {
                $query->where('resource_type', $resourceType);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->where('created_at', '>=', Carbon::parse($dateFrom));
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->where('created_at', '<=', Carbon::parse($dateTo));
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);

        return response()->json([
            'success' => true,
            'data' => $logs,
            'message' => 'Audit logs retrieved successfully'
        ]);
    }

    /**
     * Display the specified audit log.
     */
    public function show(AuditLog $auditLog): JsonResponse
    {
        $auditLog->load(['user', 'project']);

        return response()->json([
            'success' => true,
            'data' => $auditLog,
            'message' => 'Audit log retrieved successfully'
        ]);
    }

    /**
     * Get audit log statistics and analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subDays(30);
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();

        $analytics = [
            'total_logs' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'success_logs' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])->where('status', 'success')->count(),
            'failed_logs' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])->where('status', 'failed')->count(),
            'warning_logs' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])->where('status', 'warning')->count(),
            
            'actions_breakdown' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->get(),
            
            'resource_types_breakdown' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('resource_type')
                ->select('resource_type', DB::raw('count(*) as count'))
                ->groupBy('resource_type')
                ->orderBy('count', 'desc')
                ->get(),
            
            'daily_activity' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            
            'top_users' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('user_id')
                ->with('user:id,name,email')
                ->select('user_id', DB::raw('count(*) as count'))
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            
            'top_projects' => AuditLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('project_id')
                ->with('project:id,name')
                ->select('project_id', DB::raw('count(*) as count'))
                ->groupBy('project_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics,
            'message' => 'Analytics retrieved successfully'
        ]);
    }

    /**
     * Get compliance report for specific period.
     */
    public function complianceReport(Request $request): JsonResponse
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subDays(90);
        $dateTo = $request->date_to ? Carbon::parse($request->dateTo) : Carbon::now();

        $compliance = [
            'period' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d')
            ],
            
            'user_activities' => [
                'total_users' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'active_users' => User::where('is_active', true)->whereBetween('last_login_at', [$dateFrom, $dateTo])->count(),
                'new_registrations' => User::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'deleted_accounts' => AuditLog::where('action', 'user_deleted')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count()
            ],
            
            'project_activities' => [
                'total_projects' => Project::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'active_projects' => Project::where('status', 'active')->whereBetween('last_activity_at', [$dateFrom, $dateTo])->count(),
                'archived_projects' => Project::where('status', 'archived')->whereBetween('updated_at', [$dateFrom, $dateTo])->count(),
                'deleted_projects' => AuditLog::where('action', 'project_deleted')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count()
            ],
            
            'file_operations' => [
                'files_obfuscated' => AuditLog::whereIn('action', ['file_obfuscated', 'project_files_obfuscated'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'files_deobfuscated' => AuditLog::whereIn('action', ['file_deobfuscated', 'project_files_deobfuscated'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'files_restored' => AuditLog::where('action', 'file_restored')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count()
            ],
            
            'security_events' => [
                'failed_operations' => AuditLog::where('status', 'failed')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'api_key_changes' => AuditLog::where('action', 'api_key_regenerated')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'suspicious_activities' => AuditLog::where('status', 'warning')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count()
            ],
            
            'data_retention' => [
                'backups_created' => AuditLog::where('action', 'backup_created')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'backups_restored' => AuditLog::where('action', 'backup_restored')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count(),
                'files_deleted' => AuditLog::where('action', 'file_deleted')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $compliance,
            'message' => 'Compliance report generated successfully'
        ]);
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request): JsonResponse
    {
        $logs = AuditLog::with(['user', 'project'])
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->project_id, function ($query, $projectId) {
                $query->where('project_id', $projectId);
            })
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->where('created_at', '>=', Carbon::parse($dateFrom));
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->where('created_at', '<=', Carbon::parse($dateTo));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $csvData = [];
        $csvData[] = [
            'ID', 'User', 'Project', 'Action', 'Resource Type', 'Resource ID',
            'Status', 'IP Address', 'User Agent', 'Created At', 'Details'
        ];

        foreach ($logs as $log) {
            $csvData[] = [
                $log->id,
                $log->user ? $log->user->email : 'N/A',
                $log->project ? $log->project->name : 'N/A',
                $log->action,
                $log->resource_type ?? 'N/A',
                $log->resource_id ?? 'N/A',
                $log->status,
                $log->ip_address ?? 'N/A',
                $log->user_agent ?? 'N/A',
                $log->created_at->format('Y-m-d H:i:s'),
                json_encode($log->details)
            ];
        }

        $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        // Log the export action
        AuditLog::logAction(
            $request->user()?->id,
            'audit_logs_exported',
            'audit_log',
            null,
            ['filename' => $filename, 'records_count' => count($logs)]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'filename' => $filename,
                'download_url' => url('storage/exports/' . $filename),
                'records_count' => count($logs)
            ],
            'message' => 'Audit logs exported successfully'
        ]);
    }

    /**
     * Get real-time activity feed.
     */
    public function activityFeed(Request $request): JsonResponse
    {
        $logs = AuditLog::with(['user', 'project'])
            ->when($request->limit, function ($query, $limit) {
                $query->limit($limit);
            }, function ($query) {
                $query->limit(20);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $feed = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'status' => $log->status,
                'user' => $log->user ? [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email
                ] : null,
                'project' => $log->project ? [
                    'id' => $log->project->id,
                    'name' => $log->project->name
                ] : null,
                'resource_type' => $log->resource_type,
                'resource_id' => $log->resource_id,
                'details' => $log->details,
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->diffForHumans(),
                'timestamp' => $log->created_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $feed,
            'message' => 'Activity feed retrieved successfully'
        ]);
    }

    /**
     * Clean up old audit logs (for maintenance).
     */
    public function cleanup(Request $request): JsonResponse
    {
        $daysToKeep = $request->days ?? 365; // Default: keep 1 year
        $cutoffDate = Carbon::now()->subDays($daysToKeep);

        $deletedCount = AuditLog::where('created_at', '<', $cutoffDate)->delete();

        // Log the cleanup action
        AuditLog::logAction(
            $request->user()?->id,
            'audit_logs_cleanup',
            'audit_log',
            null,
            ['days_kept' => $daysToKeep, 'deleted_count' => $deletedCount]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->format('Y-m-d'),
                'days_kept' => $daysToKeep
            ],
            'message' => 'Audit logs cleanup completed successfully'
        ]);
    }
}
