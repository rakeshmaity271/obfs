<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\DeobfuscatorService;

class AppDeobfuscateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deobfuscate:app-deploy 
                            {--output= : Output directory for deployment package}
                            {--create-package : Create a deployment package (ZIP file)}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     */
    protected $description = 'Deploy entire Laravel application with secure deobfuscation (excludes vendor, node_modules, etc.)';

    /**
     * Execute the console command.
     */
    public function handle(DeobfuscatorService $deobfuscator): int
    {
        $output = $this->option('output');
        $createPackage = $this->option('create-package');
        $force = $this->option('force');

        $source = getcwd();
        $this->info("🚀  LARAVEL APPLICATION SECURE DEOBFUSCATION");
        $this->info("🔓  Source: {$source}");
        $this->info("🔓  Excluding: vendor, node_modules, storage, .git, .env, etc.");
        
        if (!$force && !$this->confirm('Are you ready to deobfuscate the entire application securely?')) {
            $this->info('Application deobfuscation cancelled.');
            return Command::SUCCESS;
        }

        // Call the secure deobfuscate command with app-only flag
        $command = "deobfuscate:secure-deploy {$source}";
        if ($output) {
            $command .= " --output={$output}";
        }
        $command .= " --app-only";
        if ($createPackage) {
            $command .= " --create-package";
        }

        $this->info("🔓  Executing: php artisan {$command}");
        
        return $this->call('deobfuscate:secure-deploy', [
            'source' => $source,
            '--output' => $output,
            '--app-only' => true,
            '--create-package' => $createPackage
        ]);
    }
}
