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

        $status = $licenseService->getStatus();
        $this->info("Status: {$status}");

        if ($licenseService->isValid()) {
            $info = $licenseService->getLicenseInfo();
            $this->info("Customer: {$info['customer']}");
            $this->info("Plan: {$info['plan']}");
            
            if ($info['expires_at']) {
                $expiresAt = date('Y-m-d H:i:s', $info['expires_at']);
                $this->info("Expires: {$expiresAt}");
            }

            $this->info("Features: " . implode(', ', $info['features']));
            
            if ($info['max_files'] > 0) {
                $this->info("File Limit: {$info['max_files']} files");
            } else {
                $this->info("File Limit: Unlimited");
            }

            if ($info['max_file_size'] > 0) {
                $this->info("Size Limit: " . $this->formatBytes($info['max_file_size']));
            } else {
                $this->info("Size Limit: Unlimited");
            }
        } else {
            $this->warn('⚠️  No valid license found!');
            $this->info('To activate, set OBFUSCATOR_LICENSE_KEY in your .env file');
            $this->info('Demo keys available: DEMO-1234-5678-9ABC, TRIAL-ABCD-EFGH-IJKL, PRO-1234-5678-9ABC');
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

        // Validate the license key locally
        $demoLicenses = [
            'DEMO-1234-5678-9ABC' => 'Demo License (30 days, limited features)',
            'TRIAL-ABCD-EFGH-IJKL' => 'Trial License (7 days, full features)',
            'PRO-1234-5678-9ABC' => 'Professional License (1 year, unlimited)',
        ];

        if (isset($demoLicenses[$licenseKey])) {
            $this->info('✅ License Key Valid!');
            $this->info("Type: {$demoLicenses[$licenseKey]}");
            $this->info('');
            $this->info('To activate this license, add to your .env file:');
            $this->info("OBFUSCATOR_LICENSE_KEY={$licenseKey}");
        } else {
            $this->error('❌ Invalid License Key!');
            $this->info('Available demo keys:');
            foreach ($demoLicenses as $key => $description) {
                $this->info("  {$key} - {$description}");
            }
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
