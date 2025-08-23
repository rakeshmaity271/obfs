<?php

namespace LaravelObfuscator\LaravelObfuscator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LicenseService
{
    private string $licenseKey;
    private bool $isValid = false;
    private string $cacheKey = 'laravel_obfuscator_license';

    public function __construct(?string $licenseKey = null)
    {
        if ($licenseKey === null) {
            $this->licenseKey = '';
        } else {
            $this->licenseKey = $licenseKey;
        }
        // Don't validate in constructor - do it lazily when needed
    }

    /**
     * Check if license is valid
     */
    public function isValid(): bool
    {
        // Load license key if not already loaded
        if (empty($this->licenseKey)) {
            $this->loadLicenseKey();
        }
        
        // Validate if not already validated
        if (!$this->isValid && !empty($this->licenseKey)) {
            $this->validateLicense();
        }
        
        return $this->isValid;
    }

    /**
     * Check if specific feature is available
     */
    public function hasFeature(string $feature): bool
    {
        return $this->isValid;
    }

    /**
     * Get license information
     */
    public function getLicenseInfo(): array
    {
        if (!$this->isValid) {
            return [
                'valid' => false,
                'message' => 'No valid license key found. Run: php artisan obfuscate:generate-key'
            ];
        }

        return [
            'valid' => true,
            'message' => 'License key is valid',
            'key' => $this->licenseKey
        ];
    }

    /**
     * Check usage limits (always allowed with valid key)
     */
    public function checkUsageLimits(int $fileCount = 0, int $fileSize = 0): array
    {
        if (!$this->isValid) {
            return ['allowed' => false, 'message' => 'Invalid license'];
        }

        return ['allowed' => true, 'message' => 'Usage allowed'];
    }

    /**
     * Load license key from config
     */
    private function loadLicenseKey(): void
    {
        try {
            if (function_exists('config')) {
                $this->licenseKey = config('laravel-obfuscator.license.license_key', '');
            }
        } catch (\Exception $e) {
            // Config not available, keep empty key
        }
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

        // Check cache first (if available)
        try {
            if (class_exists('Illuminate\Support\Facades\Cache')) {
                $cached = Cache::get($this->cacheKey);
                if ($cached && $cached['key'] === $this->licenseKey) {
                    $this->isValid = true;
                    return;
                }
            }
        } catch (\Exception $e) {
            // Cache not available, continue with validation
        }

        // Simple validation: any non-empty key is valid
        if (strlen($this->licenseKey) >= 16) {
            $this->isValid = true;
            
            // Cache for 24 hours (if available)
            try {
                if (class_exists('Illuminate\Support\Facades\Cache')) {
                    Cache::put($this->cacheKey, [
                        'key' => $this->licenseKey,
                        'validated_at' => time()
                    ], 86400);
                }
            } catch (\Exception $e) {
                // Cache not available, continue without caching
            }
            
            return;
        }

        $this->isValid = false;
    }

    /**
     * Generate a new license key
     */
    public function generateKey(): string
    {
        $key = 'OBF-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));
        
        // Clear cache (if available)
        try {
            if (class_exists('Illuminate\Support\Facades\Cache')) {
                Cache::forget($this->cacheKey);
            }
        } catch (\Exception $e) {
            // Cache not available, continue without clearing
        }
        
        return $key;
    }

    /**
     * Set license key (for testing)
     */
    public function setKey(string $key): void
    {
        $this->licenseKey = $key;
        $this->validateLicense();
    }
}
