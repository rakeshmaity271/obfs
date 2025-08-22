<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateLicenseCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:generate-license 
                            {plan : License plan (demo, trial, pro, custom)}
                            {--days=30 : Number of days until expiration}
                            {--files=10 : Maximum number of files (0 for unlimited)}
                            {--size=1 : Maximum file size in MB (0 for unlimited)}
                            {--customer= : Customer name for the license}';

    /**
     * The console command description.
     */
    protected $description = 'Generate custom license keys for LaravelObfuscator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $plan = strtolower($this->argument('plan'));
        $days = (int) $this->option('days');
        $maxFiles = (int) $this->option('files');
        $maxSizeMB = (int) $this->option('size');
        $customer = $this->option('customer') ?: 'Custom User';

        // Validate plan
        if (!in_array($plan, ['demo', 'trial', 'pro', 'custom'])) {
            $this->error('Invalid plan. Available plans: demo, trial, pro, custom');
            return Command::FAILURE;
        }

        // Generate license key
        $licenseKey = $this->generateLicenseKey($plan, $days);
        
        // Calculate expiration
        if ($days > 0) {
            $expirationDate = date('Y-m-d H:i:s', time() + ($days * 24 * 60 * 60));
        } else {
            $expirationDate = 'Never (Unlimited)';
        }
        
        // Get plan features
        $features = $this->getPlanFeatures($plan);
        
        // Display generated license
        $this->info('🔑 Generated License Key');
        $this->info('========================');
        $this->info("License Key: {$licenseKey}");
        $this->info("Plan: " . ucfirst($plan));
        $this->info("Customer: {$customer}");
        $this->info("Expires: {$expirationDate}");
        if ($days == 0) {
            $this->info("Duration: Unlimited (Never Expires)");
        } else {
            $this->info("Duration: {$days} days");
        }
        $this->info("Features: " . implode(', ', $features));
        $this->info("File Limit: " . ($maxFiles > 0 ? "{$maxFiles} files" : "Unlimited"));
        $this->info("Size Limit: " . ($maxSizeMB > 0 ? "{$maxSizeMB} MB" : "Unlimited"));
        
        $this->info('');
        $this->info('📝 To use this license:');
        $this->info('1. Add to your .env file:');
        $this->info("   OBFUSCATOR_LICENSE_KEY={$licenseKey}");
        $this->info('');
        $this->info('2. Test the license:');
        $this->info("   php artisan obfuscate:license status");
        $this->info('');
        $this->info('3. Start obfuscating:');
        $this->info("   php artisan obfuscate:file example.php");
        
        // Save to licenses.txt for reference
        $this->saveLicenseToFile($licenseKey, $plan, $customer, $expirationDate, $features, $maxFiles, $maxSizeMB);
        
        return Command::SUCCESS;
    }

    /**
     * Generate a unique license key
     */
    private function generateLicenseKey(string $plan, int $days): string
    {
        if ($days > 0) {
            $timestamp = time() + ($days * 24 * 60 * 60);
        } else {
            $timestamp = 0; // 0 means unlimited/never expires
        }
        
        $random = Str::random(8);
        
        return strtoupper($plan) . '-' . $timestamp . '-' . $random;
    }

    /**
     * Get features for the plan
     */
    private function getPlanFeatures(string $plan): array
    {
        $features = [
            'demo' => ['basic_obfuscation', 'deobfuscation'],
            'trial' => ['basic_obfuscation', 'deobfuscation', 'advanced_obfuscation'],
            'pro' => ['basic_obfuscation', 'deobfuscation', 'advanced_obfuscation', 'enterprise_features'],
            'custom' => ['basic_obfuscation', 'deobfuscation', 'advanced_obfuscation'],
        ];

        return $features[$plan] ?? $features['demo'];
    }

    /**
     * Save license to file for reference
     */
    private function saveLicenseToFile(string $key, string $plan, string $customer, string $expires, array $features, int $maxFiles, int $maxSizeMB): void
    {
        $licenseData = [
            'Generated: ' . date('Y-m-d H:i:s'),
            'License Key: ' . $key,
            'Plan: ' . ucfirst($plan),
            'Customer: ' . $customer,
            'Expires: ' . $expires,
            'Features: ' . implode(', ', $features),
            'File Limit: ' . ($maxFiles > 0 ? "{$maxFiles} files" : "Unlimited"),
            'Size Limit: ' . ($maxSizeMB > 0 ? "{$maxSizeMB} MB" : "Unlimited"),
            '---'
        ];

        $content = implode("\n", $licenseData) . "\n";
        file_put_contents('generated_licenses.txt', $content, FILE_APPEND | LOCK_EX);
        
        $this->info('💾 License saved to generated_licenses.txt');
    }
}
