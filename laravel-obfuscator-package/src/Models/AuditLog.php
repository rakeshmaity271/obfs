<?php

namespace LaravelObfuscator\LaravelObfuscator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'action',
        'resource_type',
        'resource_id',
        'details',
        'ip_address',
        'user_agent',
        'status',
        'error_message'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project associated with this action
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Log an obfuscation action
     */
    public static function logObfuscation(
        int $userId,
        ?int $projectId,
        string $action,
        string $resourceType,
        $resourceId,
        array $details = [],
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'project_id' => $projectId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $details,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => 'success'
        ]);
    }

    /**
     * Log an error action
     */
    public static function logError(
        int $userId,
        ?int $projectId,
        string $action,
        string $resourceType,
        $resourceId,
        string $errorMessage,
        array $details = [],
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'project_id' => $projectId,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'details' => $details,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => 'error',
            'error_message' => $errorMessage
        ]);
    }

    /**
     * Log file upload
     */
    public static function logFileUpload(
        int $userId,
        ?int $projectId,
        string $fileName,
        int $fileSize,
        string $fileType,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::logObfuscation(
            $userId,
            $projectId,
            'file_upload',
            'file',
            $fileName,
            [
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'timestamp' => now()->toISOString()
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Log file obfuscation
     */
    public static function logFileObfuscation(
        int $userId,
        ?int $projectId,
        string $fileName,
        string $obfuscationLevel,
        bool $backupCreated,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::logObfuscation(
            $userId,
            $projectId,
            'file_obfuscation',
            'file',
            $fileName,
            [
                'file_name' => $fileName,
                'obfuscation_level' => $obfuscationLevel,
                'backup_created' => $backupCreated,
                'timestamp' => now()->toISOString()
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Log file deobfuscation
     */
    public static function logFileDeobfuscation(
        int $userId,
        ?int $projectId,
        string $fileName,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::logObfuscation(
            $userId,
            $projectId,
            'file_deobfuscation',
            'file',
            $fileName,
            [
                'file_name' => $fileName,
                'timestamp' => now()->toISOString()
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Log project creation
     */
    public static function logProjectCreation(
        int $userId,
        int $projectId,
        string $projectName,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::logObfuscation(
            $userId,
            $projectId,
            'project_creation',
            'project',
            $projectId,
            [
                'project_name' => $projectName,
                'timestamp' => now()->toISOString()
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Log project modification
     */
    public static function logProjectModification(
        int $userId,
        int $projectId,
        string $projectName,
        array $changes,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::logObfuscation(
            $userId,
            $projectId,
            'project_modification',
            'project',
            $projectId,
            [
                'project_name' => $projectName,
                'changes' => $changes,
                'timestamp' => now()->toISOString()
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Log user authentication
     */
    public static function logAuthentication(
        int $userId,
        string $action,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::logObfuscation(
            $userId,
            null,
            $action,
            'user',
            $userId,
            [
                'timestamp' => now()->toISOString()
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Log API usage
     */
    public static function logApiUsage(
        int $userId,
        ?int $projectId,
        string $endpoint,
        string $method,
        int $responseTime,
        int $responseCode,
        string $ipAddress = null,
        string $userAgent = null
    ): self {
        return self::logObfuscation(
            $userId,
            $projectId,
            'api_usage',
            'api',
            $endpoint,
            [
                'endpoint' => $endpoint,
                'method' => $method,
                'response_time' => $responseTime,
                'response_code' => $responseCode,
                'timestamp' => now()->toISOString()
            ],
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Get audit logs for compliance reporting
     */
    public static function getComplianceReport(
        ?int $userId = null,
        ?int $projectId = null,
        ?string $action = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = self::with(['user', 'project']);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        if ($action) {
            $query->where('action', $action);
        }
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        return [
            'total_actions' => $logs->count(),
            'successful_actions' => $logs->where('status', 'success')->count(),
            'failed_actions' => $logs->where('status', 'error')->count(),
            'actions_by_type' => $logs->groupBy('action')->map->count(),
            'actions_by_user' => $logs->groupBy('user.name')->map->count(),
            'actions_by_project' => $logs->groupBy('project.name')->map->count(),
            'logs' => $logs
        ];
    }

    /**
     * Get GDPR-compliant user data export
     */
    public static function getGdprExport(int $userId): array
    {
        $logs = self::where('user_id', $userId)
            ->with(['user', 'project'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return [
            'user_id' => $userId,
            'export_date' => now()->toISOString(),
            'total_actions' => $logs->count(),
            'actions' => $logs->map(function ($log) {
                return [
                    'action' => $log->action,
                    'resource_type' => $log->resource_type,
                    'resource_id' => $log->resource_id,
                    'timestamp' => $log->created_at->toISOString(),
                    'ip_address' => $log->ip_address,
                    'details' => $log->details
                ];
            })
        ];
    }

    /**
     * Clean old audit logs for data retention compliance
     */
    public static function cleanOldLogs(int $daysToKeep = 365): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        return self::where('created_at', '<', $cutoffDate)->delete();
    }
}
