<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelObfuscator\LaravelObfuscator\Services\LicenseService;

class RestoreCommand extends Command
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        parent::__construct();
        $this->licenseService = $licenseService;
    }
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:restore {backup_file_name : Name of the backup file to restore}';

    /**
     * The console command description.
     */
    protected $description = 'Restore a backed-up file from obfuscation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check license before restore
        if (!$this->licenseService->isValid()) {
            $this->error('❌ Invalid or expired license. Please check your LaravelObfuscator license.');
            $this->info('💡 Use: php artisan obfuscate:license status');
            return Command::FAILURE;
        }

        $backupFileName = $this->argument('backup_file_name');
        
        try {
            $backupDir = storage_path('app/obfuscator_backups');
            
            if (!File::exists($backupDir)) {
                $this->error('Backup directory not found. No backups have been created yet.');
                return Command::FAILURE;
            }
            
            // Find the backup file
            $backupPath = $backupDir . DIRECTORY_SEPARATOR . $backupFileName;
            
            if (!File::exists($backupPath)) {
                $this->error("Backup file not found: {$backupFileName}");
                $this->info('Available backup files:');
                
                $backupFiles = File::files($backupDir);
                if (empty($backupFiles)) {
                    $this->warn('No backup files found in backup directory.');
                } else {
                    foreach ($backupFiles as $file) {
                        $this->line('  - ' . $file->getFilename());
                    }
                }
                
                return Command::FAILURE;
            }
            
            // Extract original file path from backup filename
            $originalPath = $this->extractOriginalPath($backupFileName);
            
            if (!$originalPath) {
                $this->error('Could not determine original file path from backup filename.');
                return Command::FAILURE;
            }
            
            $this->info("Restoring backup: {$backupFileName}");
            $this->info("Original file: {$originalPath}");
            
            // Create directory if it doesn't exist
            $originalDir = dirname($originalPath);
            if (!File::exists($originalDir)) {
                File::makeDirectory($originalDir, 0755, true);
            }
            
            // Restore the file
            File::copy($backupPath, $originalPath);
            
            $this->info('✅ File restored successfully!');
            $this->info("Restored to: {$originalPath}");
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Restore failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Extract original file path from backup filename
     */
    private function extractOriginalPath(string $backupFileName): ?string
    {
        // Remove 'backup_' prefix and timestamp
        if (preg_match('/^backup_\d+_(.+)$/', $backupFileName, $matches)) {
            $originalFileName = $matches[1];
            
            // Try to find the original file by looking for files with similar names
            $basePath = base_path();
            $foundFiles = $this->findSimilarFiles($basePath, $originalFileName);
            
            if (!empty($foundFiles)) {
                // Return the first match (you could make this more sophisticated)
                return $foundFiles[0];
            }
        }
        
        return null;
    }
    
    /**
     * Find files with similar names in the project
     */
    private function findSimilarFiles(string $basePath, string $fileName): array
    {
        $foundFiles = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === $fileName) {
                $foundFiles[] = $file->getPathname();
            }
        }
        
        return $foundFiles;
    }
}
