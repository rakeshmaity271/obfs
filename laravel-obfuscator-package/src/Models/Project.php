<?php

namespace LaravelObfuscator\LaravelObfuscator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'status',
        'settings',
        'storage_used',
        'file_count',
        'last_activity_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'last_activity_at' => 'datetime'
    ];

    /**
     * Get the user who owns this project
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get project files
     */
    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    /**
     * Get project obfuscation jobs
     */
    public function obfuscationJobs(): HasMany
    {
        return $this->obfuscationJobs()->hasMany(ObfuscationJob::class);
    }

    /**
     * Get project backups
     */
    public function backups(): HasMany
    {
        return $this->hasMany(ProjectBackup::class);
    }

    /**
     * Check if project is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if project is archived
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Archive the project
     */
    public function archive(): void
    {
        $this->status = 'archived';
        $this->save();
    }

    /**
     * Activate the project
     */
    public function activate(): void
    {
        $this->status = 'active';
        $this->save();
    }

    /**
     * Update project activity
     */
    public function updateActivity(): void
    {
        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Get project statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_files' => $this->files()->count(),
            'obfuscated_files' => $this->files()->where('is_obfuscated', true)->count(),
            'backup_files' => $this->backups()->count(),
            'storage_used' => $this->storage_used,
            'last_activity' => $this->last_activity_at,
            'file_types' => $this->files()->selectRaw('file_type, COUNT(*) as count')
                ->groupBy('file_type')
                ->pluck('count', 'file_type')
                ->toArray()
        ];
    }

    /**
     * Check if project has exceeded storage limit
     */
    public function hasExceededStorageLimit(): bool
    {
        $limit = config('laravel-obfuscator.project_storage_limit', 536870912); // 512MB default
        return $this->storage_used > $limit;
    }

    /**
     * Get project's remaining storage
     */
    public function getRemainingStorage(): int
    {
        $limit = config('laravel-obfuscator.project_storage_limit', 536870912);
        return max(0, $limit - $this->storage_used);
    }

    /**
     * Update project storage usage
     */
    public function updateStorageUsage(): void
    {
        $this->storage_used = $this->files()->sum('file_size');
        $this->file_count = $this->files()->count();
        $this->save();
    }

    /**
     * Get project settings with defaults
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set project setting
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get default obfuscation settings for this project
     */
    public function getDefaultObfuscationSettings(): array
    {
        return [
            'level' => $this->getSetting('default_obfuscation_level', 'basic'),
            'create_backup' => $this->getSetting('default_create_backup', true),
            'preserve_structure' => $this->getSetting('preserve_structure', true),
            'exclude_patterns' => $this->getSetting('exclude_patterns', []),
            'include_patterns' => $this->getSetting('include_patterns', ['*.php'])
        ];
    }

    /**
     * Get project file patterns
     */
    public function getFilePatterns(): array
    {
        return [
            'include' => $this->getSetting('include_patterns', ['*.php']),
            'exclude' => $this->getSetting('exclude_patterns', [
                'vendor/**/*',
                'node_modules/**/*',
                'tests/**/*',
                '*.test.php',
                '*.spec.php'
            ])
        ];
    }

    /**
     * Check if file should be included in project
     */
    public function shouldIncludeFile(string $filePath): bool
    {
        $patterns = $this->getFilePatterns();
        
        // Check include patterns
        $included = false;
        foreach ($patterns['include'] as $pattern) {
            if (fnmatch($pattern, basename($filePath))) {
                $included = true;
                break;
            }
        }
        
        if (!$included) {
            return false;
        }
        
        // Check exclude patterns
        foreach ($patterns['exclude'] as $pattern) {
            if (fnmatch($pattern, $filePath)) {
                return false;
            }
        }
        
        return true;
    }
}
