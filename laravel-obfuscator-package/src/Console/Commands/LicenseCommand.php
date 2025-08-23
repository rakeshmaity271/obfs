<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\LicenseService;

class LicenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:license 
                            {action=status : Action to perform (status, validate, info)}
                            {--key= : License key to validate}';

    /**
     * The console command description.
     */
    protected $description = 'Manage and check LaravelObfuscator license status';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService): int
    {
        $action = $this->argument('action');
        $licenseKey = $this->option('key');

        switch ($action) {
            case 'status':
                return $this->showStatus($licenseService);
            case 'validate':
                return $this->validateLicense($licenseService, $licenseKey);
            case 'info':
                return $this->showInfo($licenseService);
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: status, validate, info");
                return Command::FAILURE;
        }
    }

    /**
     * Show license status
     */
    private function showStatus(LicenseService $licenseService): int
    {
        $this->info('🔐 LaravelObfuscator License Status');
        $this->info('=====================================');

        if ($licenseService->isValid()) {
            $info = $licenseService->getLicenseInfo();
            $this->info("Status: ✅ Valid License");
            $this->info("Key: {$info['key']}");
            $this->info("Message: {$info['message']}");
        } else {
            $this->warn('⚠️  No valid license found!');
            $this->info('To activate, run: php artisan obfuscate:generate-key');
            $this->info('Then add the key to your .env file');
        }

        return Command::SUCCESS;
    }

    /**
     * Validate a license key
     */
    private function validateLicense(LicenseService $licenseService, ?string $licenseKey): int
    {
        if (!$licenseKey) {
            $this->error('Please provide a license key with --key option');
            return Command::FAILURE;
        }

        $this->info('🔍 Validating License Key...');
        $this->info("Key: {$licenseKey}");

        // Set the key temporarily and validate
        $licenseService->setKey($licenseKey);
        
        if ($licenseService->isValid()) {
            $this->info('✅ License Key Valid!');
            $this->info('Type: Generated License');
            $this->info('');
            $this->info('To activate this license, add to your .env file:');
            $this->info("OBFUSCATOR_LICENSE_KEY={$licenseKey}");
        } else {
            $this->error('❌ Invalid License Key!');
            $this->info('Generate a new key with: php artisan obfuscate:generate-key');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Show detailed license information
     */
    private function showInfo(LicenseService $licenseService): int
    {
        $this->info('📋 LaravelObfuscator License Information');
        $this->info('==========================================');

        if (!$licenseService->isValid()) {
            $this->warn('⚠️  No valid license found');
            return Command::SUCCESS;
        }

        $info = $licenseService->getLicenseInfo();
        
        $this->table(
            ['Property', 'Value'],
            [
                ['Customer', $info['customer']],
                ['Plan', $info['plan']],
                ['Status', 'Active'],
                ['Expires', $info['expires_at'] ? date('Y-m-d H:i:s', $info['expires_at']) : 'Never'],
                ['Features', implode(', ', $info['features'])],
                ['File Limit', $info['max_files'] > 0 ? $info['max_files'] : 'Unlimited'],
                ['Size Limit', $info['max_file_size'] > 0 ? $this->formatBytes($info['max_file_size']) : 'Unlimited'],
            ]
        );

        return Command::SUCCESS;
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
}
