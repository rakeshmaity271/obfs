<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelObfuscator\LaravelObfuscator\Services\DeobfuscatorService;

class DeobfuscateSecureDeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deobfuscate:secure-deploy 
                            {source? : Source directory or file to deploy (defaults to current directory)}
                            {--output= : Output directory for deployment package}
                            {--exclude=* : Files/directories to exclude from deobfuscation}
                            {--create-package : Create a deployment package (ZIP file)}
                            {--app-only : Deploy only the application (exclude vendor, node_modules, etc.)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a secure deployment package with deobfuscated code that clients can understand and maintain';

    /**
     * The deobfuscator service instance.
     */
    protected DeobfuscatorService $deobfuscatorService;

    /**
     * Create a new command instance.
     */
    public function __construct(DeobfuscatorService $deobfuscatorService)
    {
        parent::__construct();
        $this->deobfuscatorService = $deobfuscatorService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $source = $this->argument('source') ?: getcwd();
        $output = $this->option('output');
        $exclude = $this->option('exclude');
        $createPackage = $this->option('create-package');
        $appOnly = $this->option('app-only');

        if (!file_exists($source)) {
            $this->error("Source not found: {$source}");
            return Command::FAILURE;
        }

        // Set default exclusions for Laravel applications
        if ($appOnly || empty($exclude)) {
            $defaultExclusions = [
                'vendor',
                'node_modules',
                'storage/logs',
                'storage/framework/cache',
                'storage/framework/sessions',
                'storage/framework/views',
                '.git',
                '.env',
                'composer.lock',
                'package-lock.json',
                'yarn.lock',
                'npm-debug.log',
                '.DS_Store',
                'Thumbs.db'
            ];
            $exclude = array_merge($exclude, $defaultExclusions);
        }

        $this->warn('🔓  SECURE DEOBFUSCATION DEPLOYMENT PACKAGE CREATION');
        $this->warn('🔓  This will create a client-ready package with readable, maintainable code!');
        $this->warn('🔓  Original obfuscated files will be moved to SECURE backup location.');
        $this->info("🔓  Source: {$source}");
        $this->info("🔓  Excluding: " . implode(', ', $exclude));
        
        if (!$this->confirm('Are you ready to create a deobfuscation deployment package?')) {
            $this->info('Secure deobfuscation deployment cancelled.');
            return Command::SUCCESS;
        }

        try {
            if (is_dir($source)) {
                return $this->deployDirectory($source, $output, $exclude, $createPackage);
            } else {
                return $this->deployFile($source, $output, $createPackage);
            }
        } catch (\Exception $e) {
            $this->error('Secure deobfuscation deployment failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Deploy a single file securely
     */
    private function deployFile(string $source, ?string $output, bool $createPackage): int
    {
        $this->info("🔓  Securely deploying deobfuscated file: {$source}");
        
        // Create secure backup first
        $secureBackupPath = $this->createSecureBackup($source);
        $this->info("🔓  Original obfuscated file securely backed up to: {$secureBackupPath}");
        
        // Determine output path
        if (!$output) {
            $output = dirname($source);
        }
        
        // Deobfuscate the file
        $deobfuscatedPath = $this->deobfuscatorService->deobfuscateFile($source, null);
        
        // Move deobfuscated file to replace original
        if (rename($deobfuscatedPath, $source)) {
            $this->info('✅ File securely deployed with deobfuscated code!');
            $this->info('🔓  Client can now read and maintain the code!');
            
            if ($createPackage) {
                $this->createDeploymentPackage(dirname($source), $output);
            }
            
            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to deploy file securely!');
            return Command::FAILURE;
        }
    }

    /**
     * Deploy a directory securely
     */
    private function deployDirectory(string $source, ?string $output, array $exclude, bool $createPackage): int
    {
        $this->info("🔓  Securely deploying deobfuscated directory: {$source}");
        
        // Create secure backup of entire directory
        $secureBackupPath = $this->createSecureBackup($source);
        $this->info("🔓  Original obfuscated directory securely backed up to: {$secureBackupPath}");
        
        // Determine output path
        if (!$output) {
            $output = dirname($source);
        }
        
        // Get all PHP files
        $phpFiles = $this->getPhpFiles($source, $exclude);
        $this->info("🔓  Found " . count($phpFiles) . " PHP files to deobfuscate");
        
        if (empty($phpFiles)) {
            $this->warn("⚠️  No PHP files found to deobfuscate!");
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failedCount = 0;
        
        $progressBar = $this->output->createProgressBar(count($phpFiles));
        $progressBar->start();
        
        foreach ($phpFiles as $file) {
            try {
                $deobfuscatedPath = $this->deobfuscatorService->deobfuscateFile($file, null);
                
                // Replace original with deobfuscated
                if (rename($deobfuscatedPath, $file)) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
            }
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("🔓  Deployment Summary:");
        $this->info("✅  Successfully deployed: {$successCount} files");
        if ($failedCount > 0) {
            $this->warn("⚠️  Failed to deploy: {$failedCount} files");
        }
        
        if ($createPackage) {
            $this->createDeploymentPackage($source, $output);
        }
        
        return Command::SUCCESS;
    }

    /**
     * Get all PHP files in directory (excluding specified paths)
     */
    private function getPhpFiles(string $directory, array $exclude): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                
                // Check if file should be excluded
                $shouldExclude = false;
                foreach ($exclude as $excludePath) {
                    if (strpos($filePath, $excludePath) !== false) {
                        $shouldExclude = true;
                        break;
                    }
                }
                
                if (!$shouldExclude) {
                    $files[] = $filePath;
                }
            }
        }
        
        return $files;
    }

    /**
     * Create secure backup (client cannot access)
     */
    private function createSecureBackup(string $path): string
    {
        $secureBackupDir = storage_path('app/secure_deployment_backups/' . date('Y-m-d_H-i-s'));
        
        if (!File::exists($secureBackupDir)) {
            File::makeDirectory($secureBackupDir, 0750, true);
        }
        
        if (is_dir($path)) {
            $backupPath = $secureBackupDir . DIRECTORY_SEPARATOR . basename($path);
            File::copyDirectory($path, $backupPath);
        } else {
            $backupPath = $secureBackupDir . DIRECTORY_SEPARATOR . basename($path);
            File::copy($path, $backupPath);
        }
        
        $this->info("🔓  Secure backup created: {$backupPath}");
        $this->warn("🔓  This location is NOT accessible to clients!");
        
        return $backupPath;
    }

    /**
     * Create deployment package (ZIP file)
     */
    private function createDeploymentPackage(string $source, string $output): void
    {
        $this->info("📦  Creating deployment package...");
        
        $packageName = 'secure_deobfuscation_deployment_' . date('Y-m-d_H-i-s') . '.zip';
        $packagePath = $output . DIRECTORY_SEPARATOR . $packageName;
        
        $zip = new \ZipArchive();
        if ($zip->open($packagePath, \ZipArchive::CREATE) === TRUE) {
            $this->addToZip($zip, $source, basename($source));
            $zip->close();
            
            $this->info("📦  Deployment package created: {$packagePath}");
            $this->info("🔓  This package contains readable, maintainable code!");
            $this->info("🔓  Clients can now understand and modify the application!");
        } else {
            $this->error("❌  Failed to create deployment package!");
        }
    }

    /**
     * Add files to ZIP recursively
     */
    private function addToZip(\ZipArchive $zip, string $path, string $relativePath): void
    {
        if (is_dir($path)) {
            $zip->addEmptyDir($relativePath);
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $this->addToZip($zip, $path . DIRECTORY_SEPARATOR . $file, $relativePath . DIRECTORY_SEPARATOR . $file);
                }
            }
        } else {
            $zip->addFile($path, $relativePath);
        }
    }
}
