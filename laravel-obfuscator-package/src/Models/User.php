<?php

namespace LaravelObfuscator\LaravelObfuscator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'api_key',
        'last_login_at',
        'preferences'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'preferences' => 'array'
    ];

    protected $hidden = [
        'password',
        'api_key'
    ];

    /**
     * Get user's projects
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get user's obfuscation jobs
     */
    public function obfuscationJobs(): HasMany
    {
        return $this->hasMany(ObfuscationJob::class);
    }

    /**
     * Get user's audit logs
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $rolePermissions = config('laravel-obfuscator.roles.' . $this->role . '.permissions', []);
        return in_array($permission, $rolePermissions);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is developer
     */
    public function isDeveloper(): bool
    {
        return in_array($this->role, ['admin', 'developer']);
    }

    /**
     * Check if user can obfuscate files
     */
    public function canObfuscate(): bool
    {
        return $this->hasPermission('obfuscate_files');
    }

    /**
     * Check if user can deobfuscate files
     */
    public function canDeobfuscate(): bool
    {
        return $this->hasPermission('deobfuscate_files');
    }

    /**
     * Check if user can manage projects
     */
    public function canManageProjects(): bool
    {
        return $this->hasPermission('manage_projects');
    }

    /**
     * Check if user can view audit logs
     */
    public function canViewAuditLogs(): bool
    {
        return $this->hasPermission('view_audit_logs');
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(): bool
    {
        return $this->hasPermission('manage_users');
    }

    /**
     * Generate new API key
     */
    public function generateApiKey(): string
    {
        $this->api_key = 'obf_' . bin2hex(random_bytes(32));
        $this->save();
        
        return $this->api_key;
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    /**
     * Get user's storage usage
     */
    public function getStorageUsage(): int
    {
        return $this->projects()->sum('storage_used');
    }

    /**
     * Check if user has exceeded storage limit
     */
    public function hasExceededStorageLimit(): bool
    {
        $limit = config('laravel-obfuscator.user_storage_limit', 1073741824); // 1GB default
        return $this->getStorageUsage() > $limit;
    }

    /**
     * Get user's remaining storage
     */
    public function getRemainingStorage(): int
    {
        $limit = config('laravel-obfuscator.user_storage_limit', 1073741824);
        return max(0, $limit - $this->getStorageUsage());
    }
}
