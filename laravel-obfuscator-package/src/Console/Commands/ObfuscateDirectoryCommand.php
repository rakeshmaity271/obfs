<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;

class ObfuscateDirectoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:directory 
                            {directory : Directory path to obfuscate}
                            {--backup : Create backup of original files}';

    /**
     * The console command description.
     */
    protected $description = 'Obfuscate PHP files in a specific directory';

    /**
     * Execute the console command.
     */
    public function handle(ObfuscatorService $obfuscator): int
    {
        $directory = $this->argument('directory');
        $backup = $this->option('backup');
        
        try {
            if (!is_dir($directory)) {
                $this->error("Directory not found: {$directory}");
                return Command::FAILURE;
            }
            
            $this->info("Starting obfuscation of PHP files in: {$directory}");
            
            if ($backup) {
                $this->info('Creating backups...');
            }
            
            $results = $obfuscator->obfuscateDirectory($directory, $backup);
            
            if (empty($results)) {
                $this->warn('No PHP files found in the specified directory.');
                return Command::SUCCESS;
            }
            
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
                $this->info('🎉 All files in directory obfuscated successfully!');
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
