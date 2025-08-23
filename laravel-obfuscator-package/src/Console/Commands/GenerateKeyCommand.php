<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\LicenseService;

class GenerateKeyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:generate-key';

    /**
     * The console command description.
     */
    protected $description = 'Generate a new license key for LaravelObfuscator';

    /**
     * Execute the console command.
     */
    public function handle(LicenseService $licenseService): int
    {
        $this->info('🔑 Generating LaravelObfuscator License Key...');
        
        $key = $licenseService->generateKey();
        
        $this->info('✅ License key generated successfully!');
        $this->newLine();
        $this->info('🔑 Your new license key:');
        $this->warn($key);
        $this->newLine();
        
        $this->info('📝 Add this to your .env file:');
        $this->line("OBFUSCATOR_LICENSE_KEY={$key}");
        $this->newLine();
        
        $this->info('💡 After adding the key to .env, run:');
        $this->line('php artisan config:clear');
        $this->line('php artisan obfuscate:license status');
        
        return Command::SUCCESS;
    }
}
