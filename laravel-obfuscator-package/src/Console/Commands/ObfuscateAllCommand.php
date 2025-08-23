<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use Illuminate\Support\Facades\File;

class ObfuscateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:all 
                            {--backup : Create backup of original files}
                            {--replace : Replace original files (DANGEROUS!)}';

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
        $replace = $this->option('replace');
        
        // Safety check for replace option
        if ($replace) {
            $this->warn('⚠️  DANGER: You are about to REPLACE the original files!');
            $this->warn('⚠️  This action cannot be undone!');
            
            if (!$this->confirm('Are you absolutely sure you want to replace the original files?')) {
                $this->info('Operation cancelled. Original files preserved.');
                return Command::SUCCESS;
            }
            
            // Force backup when replacing
            $backup = true;
            $this->warn('⚠️  Forcing backup creation for safety...');
        }
        
        try {
            $this->info('Starting obfuscation of all PHP files...');
            
            if ($backup) {
                $this->info('Creating backups...');
            }
            
            // Get the base path of the Laravel project
            $basePath = base_path();
            $this->info("Scanning directory: {$basePath}");
            
            return $this->normalObfuscation($obfuscator, $basePath, $backup);
            
        } catch (\Exception $e) {
            $this->error('Obfuscation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Normal obfuscation without replacement
     */
    private function normalObfuscation(ObfuscatorService $obfuscator, string $basePath, bool $backup): int
    {
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
    }
}
