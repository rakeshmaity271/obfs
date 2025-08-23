<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;

class AppDeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:app-deploy 
                            {--output= : Output directory for deployment package}
                            {--level=enterprise : Obfuscation level (basic, advanced, enterprise)}
                            {--create-package : Create a deployment package (ZIP file)}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Deploy entire Laravel application with secure obfuscation (excludes vendor, node_modules, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(ObfuscatorService $obfuscator): int
    {
        $output = $this->option('output');
        $level = $this->option('level');
        $createPackage = $this->option('create-package');
        $force = $this->option('force');

        // Check for license key in environment
        $licenseKey = env('OBFUSCATOR_LICENSE_KEY');
        if (!$licenseKey) {
            $this->error('❌ No license key found!');
            $this->info('Generate a key with: php artisan obfuscate:generate-key');
            $this->info('Then add it to your .env file');
            return Command::FAILURE;
        }

        $source = getcwd();
        $this->info("🚀  LARAVEL APPLICATION SECURE DEPLOYMENT");
        $this->info("🔒  Source: {$source}");
        $this->info("🔒  Level: {$level}");
        $this->info("🔒  Excluding: vendor, node_modules, storage, .git, .env, etc.");
        
        if (!$force && !$this->confirm('Are you ready to deploy the entire application securely?')) {
            $this->info('Application deployment cancelled.');
            return Command::SUCCESS;
        }

        // Call the secure deploy command with app-only flag
        $command = "obfuscate:secure-deploy {$source}";
        if ($output) {
            $command .= " --output={$output}";
        }
        $command .= " --level={$level}";
        $command .= " --app-only";
        if ($createPackage) {
            $command .= " --create-package";
        }

        $this->info("🔒  Executing: php artisan {$command}");
        
        return $this->call('obfuscate:secure-deploy', [
            'source' => $source,
            '--output' => $output,
            '--level' => $level,
            '--app-only' => true,
            '--create-package' => $createPackage
        ]);
    }
}
