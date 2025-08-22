<?php

namespace LaravelObfuscator\LaravelObfuscator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseService
{
    private string $licenseKey;
    private array $licenseData;
    private bool $isValid = false;
    private string $cacheKey = 'laravel_obfuscator_license';

    public function __construct()
    {
        $this->licenseKey = config('laravel-obfuscator.license_key', '');
        $this->validateLicense();
    }

    /**
     * Check if license is valid
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Check if specific feature is available
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->isValid) {
            return false;
        }

        $features = $this->licenseData['features'] ?? [];
        return in_array($feature, $features);
    }

    /**
     * Get license information
     */
    public function getLicenseInfo(): array
    {
        if (!$this->isValid) {
            return [
                'valid' => false,
                'message' => 'Invalid or expired license'
            ];
        }

        return [
            'valid' => true,
            'customer' => $this->licenseData['customer'] ?? 'Unknown',
            'plan' => $this->licenseData['plan'] ?? 'Unknown',
            'expires_at' => $this->licenseData['expires_at'] ?? null,
            'features' => $this->licenseData['features'] ?? [],
            'max_files' => $this->licenseData['max_files'] ?? 0,
            'max_file_size' => $this->licenseData['max_file_size'] ?? 0,
        ];
    }

    /**
     * Check usage limits
     */
    public function checkUsageLimits(int $fileCount = 0, int $fileSize = 0): array
    {
        if (!$this->isValid) {
            return ['allowed' => false, 'message' => 'Invalid license'];
        }

        $maxFiles = $this->licenseData['max_files'] ?? 0;
        $maxFileSize = $this->licenseData['max_file_size'] ?? 0;

        if ($maxFiles > 0 && $fileCount > $maxFiles) {
            return [
                'allowed' => false,
                'message' => "File count limit exceeded. Max: {$maxFiles}, Current: {$fileCount}"
            ];
        }

        if ($maxFileSize > 0 && $fileSize > $maxFileSize) {
            return [
                'allowed' => false,
                'message' => "File size limit exceeded. Max: " . $this->formatBytes($maxFileSize) . ", Current: " . $this->formatBytes($fileSize)
            ];
        }

        return ['allowed' => true, 'message' => 'Usage within limits'];
    }

    /**
     * Validate license key
     */
    private function validateLicense(): void
    {
        if (empty($this->licenseKey)) {
            $this->isValid = false;
            return;
        }

        // Check cache first
        $cached = Cache::get($this->cacheKey);
        if ($cached && $cached['expires_at'] > time()) {
            $this->licenseData = $cached;
            $this->isValid = true;
            return;
        }

        // Validate with license server (or local validation for demo)
        $this->licenseData = $this->validateWithServer();
        
        if ($this->isValid) {
            // Cache for 1 hour
            Cache::put($this->cacheKey, $this->licenseData, 3600);
        }
    }

    /**
     * Validate license locally
     */
    private function validateWithServer(): array
    {
        // Only use local validation - no remote server needed
        $licenseData = $this->validateLicenseLocally($this->licenseKey);
        
        if ($licenseData) {
            $this->isValid = true;
            return $licenseData;
        }

        $this->isValid = false;
        return [];
    }

    /**
     * Local license validation (for demo/testing)
     */
    private function validateLicenseLocally(string $key): ?array
    {
        // Demo license keys for testing
        $demoLicenses = [
            'DEMO-1234-5678-9ABC' => [
                'customer' => 'Demo User',
                'plan' => 'Demo',
                'expires_at' => time() + (30 * 24 * 60 * 60), // 30 days
                'features' => ['basic_obfuscation', 'deobfuscation'],
                'max_files' => 10,
                'max_file_size' => 1024 * 1024, // 1MB
            ],
            'TRIAL-ABCD-EFGH-IJKL' => [
                'customer' => 'Trial User',
                'plan' => 'Trial',
                'expires_at' => time() + (7 * 24 * 60 * 60), // 7 days
                'features' => ['basic_obfuscation', 'deobfuscation', 'advanced_obfuscation'],
                'max_files' => 50,
                'max_file_size' => 5 * 1024 * 1024, // 5MB
            ],
            'PRO-1234-5678-9ABC' => [
                'customer' => 'Pro User',
                'plan' => 'Professional',
                'expires_at' => time() + (365 * 24 * 60 * 60), // 1 year
                'features' => ['basic_obfuscation', 'deobfuscation', 'advanced_obfuscation', 'enterprise_features'],
                'max_files' => -1, // Unlimited
                'max_file_size' => -1, // Unlimited
            ]
        ];

        // Check if it's a generated key (format: PLAN-TIMESTAMP-RANDOM)
        if (preg_match('/^([A-Z]+)-\d{10}-[A-Za-z0-9]{8}$/', $key, $matches)) {
            $plan = strtolower($matches[1]);
            return $this->validateGeneratedKey($key, $plan);
        }

        return $demoLicenses[$key] ?? null;
    }

    /**
     * Validate generated license keys
     */
    private function validateGeneratedKey(string $key, string $plan): ?array
    {
        // Extract timestamp from key (format: PLAN-TIMESTAMP-RANDOM)
        $parts = explode('-', $key);
        $timestamp = (int) $parts[1];
        $currentTime = time();
        
        // Check if key is expired (0 means unlimited/never expires)
        if ($timestamp > 0 && $timestamp < $currentTime) {
            return null; // Expired
        }

        // Calculate days left (0 means unlimited)
        if ($timestamp > 0) {
            $daysLeft = ceil(($timestamp - $currentTime) / (24 * 60 * 60));
        } else {
            $daysLeft = -1; // Unlimited
        }
        
        // Generate license data based on plan
        switch ($plan) {
            case 'demo':
                return [
                    'customer' => 'Generated Demo User',
                    'plan' => 'Demo',
                    'expires_at' => $timestamp > 0 ? $timestamp : null,
                    'features' => ['basic_obfuscation', 'deobfuscation'],
                    'max_files' => 10,
                    'max_file_size' => 1024 * 1024, // 1MB
                ];
                
            case 'trial':
                return [
                    'customer' => 'Generated Trial User',
                    'plan' => 'Trial',
                    'expires_at' => $timestamp > 0 ? $timestamp : null,
                    'features' => ['basic_obfuscation', 'deobfuscation', 'advanced_obfuscation'],
                    'max_files' => 50,
                    'max_file_size' => 5 * 1024 * 1024, // 5MB
                ];
                
            case 'pro':
                return [
                    'customer' => 'Generated Pro User',
                    'plan' => 'Professional',
                    'expires_at' => $timestamp > 0 ? $timestamp : null,
                    'features' => ['basic_obfuscation', 'deobfuscation', 'advanced_obfuscation', 'enterprise_features'],
                    'max_files' => -1, // Unlimited
                    'max_file_size' => -1, // Unlimited
                ];
                
            case 'custom':
                return [
                    'customer' => 'Generated Custom User',
                    'plan' => 'Custom',
                    'expires_at' => $timestamp > 0 ? $timestamp : null,
                    'features' => ['basic_obfuscation', 'deobfuscation'],
                    'max_files' => 100,
                    'max_file_size' => 10 * 1024 * 1024, // 10MB
                ];
                
            default:
                return null;
        }
    }



    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get license status for display
     */
    public function getStatus(): string
    {
        if (!$this->isValid) {
            return '❌ Invalid License';
        }

        $expiresAt = $this->licenseData['expires_at'] ?? 0;
        
        if ($expiresAt === null) {
            return "✅ Valid (Unlimited - Never Expires)";
        }
        
        $daysLeft = ceil(($expiresAt - time()) / (24 * 60 * 60));

        if ($daysLeft <= 0) {
            return '❌ License Expired';
        } elseif ($daysLeft <= 7) {
            return "⚠️  Expires in {$daysLeft} days";
        } else {
            return "✅ Valid ({$daysLeft} days left)";
        }
    }
}
