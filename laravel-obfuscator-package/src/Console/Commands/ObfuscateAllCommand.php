<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;

class ObfuscateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:all 
                            {--backup : Create backup of original files}
                            {--replace : Replace original files (DANGEROUS!)}
                            {--secure-deploy : Secure deployment mode - replace originals with obfuscated, move originals to secure backup}';

    /**
     * The console command description.
     */
    protected $description = 'Obfuscate all PHP files in your Laravel project';

    /**
     * Execute the console command.
     */
    public function handle(ObfuscatorService $obfuscator): int
    {
        $backup = $this->option('backup');
        
        try {
            $this->info('Starting obfuscation of all PHP files...');
            
            if ($backup) {
                $this->info('Creating backups...');
            }
            
            // Get the base path of the Laravel project
            $basePath = base_path();
            $this->info("Scanning directory: {$basePath}");
            
            $results = $obfuscator->obfuscateDirectory($basePath, $backup);
            
            // Display results in a table
            $this->table(
                ['Input', 'Output', 'Status', 'Message'],
                array_map(function ($result) {
                    return [
                        $result['input'],
                        $result['output'],
                        $result['status'] === 'success' ? '✅ Success' : '❌ Error',
                        $result['message'] ?? '-'
                    ];
                }, $results)
            );
            
            $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
            $totalCount = count($results);
            
            $this->info("Completed: {$successCount}/{$totalCount} files obfuscated successfully");
            
            if ($successCount === $totalCount) {
                $this->info('🎉 All files obfuscated successfully!');
                return Command::SUCCESS;
            } else {
                $this->warn('⚠️  Some files failed to obfuscate. Check the table above for details.');
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('Obfuscation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
