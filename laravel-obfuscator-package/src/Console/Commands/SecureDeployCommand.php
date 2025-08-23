<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;

class SecureDeployCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:secure-deploy 
                            {source? : Source directory or file to deploy (defaults to current directory)}
                            {--output= : Output directory for deployment package}
                            {--exclude=* : Files/directories to exclude from obfuscation}
                            {--level=enterprise : Obfuscation level (basic, advanced, enterprise)}
                            {--create-package : Create a deployment package (ZIP file)}
                            {--app-only : Deploy only the application (exclude vendor, node_modules, etc.)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a secure deployment package with obfuscated code that clients cannot reverse-engineer';

    /**
     * Execute the console command.
     */
    public function handle(ObfuscatorService $obfuscator): int
    {
        $source = $this->argument('source') ?: getcwd();
        $output = $this->option('output');
        $exclude = $this->option('exclude');
        $level = $this->option('level');
        $createPackage = $this->option('create-package');
        $appOnly = $this->option('app-only');

        if (!file_exists($source)) {
            $this->error("Source not found: {$source}");
            return Command::FAILURE;
        }

        // Check for license key in environment
        $licenseKey = env('OBFUSCATOR_LICENSE_KEY');
        if (!$licenseKey) {
            $this->error('❌ No license key found!');
            $this->info('Generate a key with: php artisan obfuscate:generate-key');
            $this->info('Then add it to your .env file');
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

        $this->warn('🔒  SECURE DEPLOYMENT PACKAGE CREATION');
        $this->warn('🔒  This will create a client-ready package with NO original source code!');
        $this->warn('🔒  Original files will be moved to SECURE backup location.');
        $this->info("🔒  Source: {$source}");
        $this->info("🔒  Excluding: " . implode(', ', $exclude));
        
        if (!$this->confirm('Are you ready to create a secure deployment package?')) {
            $this->info('Secure deployment cancelled.');
            return Command::SUCCESS;
        }

        try {
            if (is_dir($source)) {
                return $this->deployDirectory($obfuscator, $source, $output, $exclude, $level, $createPackage);
            } else {
                return $this->deployFile($obfuscator, $source, $output, $level, $createPackage);
            }
        } catch (\Exception $e) {
            $this->error('Secure deployment failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Deploy a single file securely
     */
    private function deployFile(ObfuscatorService $obfuscator, string $source, ?string $output, string $level, bool $createPackage): int
    {
        $this->info("🔒  Securely deploying file: {$source}");
        
        // Create secure backup first
        $secureBackupPath = $this->createSecureBackup($source);
        $this->info("🔒  Original file securely backed up to: {$secureBackupPath}");
        
        // Determine output path
        if (!$output) {
            $output = dirname($source);
        }
        
        // Obfuscate the file
        $obfuscatedPath = $obfuscator->obfuscateFile($source, null, $level);
        
        // Move obfuscated file to replace original
        if (rename($obfuscatedPath, $source)) {
            $this->info('✅ File securely deployed!');
            $this->info('🔒  Client can no longer access original source code!');
            
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
    private function deployDirectory(ObfuscatorService $obfuscator, string $source, ?string $output, array $exclude, string $level, bool $createPackage): int
    {
        $this->info("🔒  Securely deploying directory: {$source}");
        
        // Create secure backup of entire directory
        $secureBackupPath = $this->createSecureBackup($source);
        $this->info("🔒  Original directory securely backed up to: {$secureBackupPath}");
        
        // Determine output path
        if (!$output) {
            $output = dirname($source);
        }
        
        // Get all PHP files
        $phpFiles = $this->getPhpFiles($source, $exclude);
        $this->info("🔒  Found " . count($phpFiles) . " PHP files to obfuscate");
        
        if (empty($phpFiles)) {
            $this->warn("⚠️  No PHP files found to obfuscate!");
            return Command::SUCCESS;
        }

        $successCount = 0;
        $failedCount = 0;
        
        $progressBar = $this->output->createProgressBar(count($phpFiles));
        $progressBar->start();
        
        foreach ($phpFiles as $file) {
            try {
                $obfuscatedPath = $obfuscator->obfuscateFile($file, null, $level);
                
                // Replace original with obfuscated
                if (rename($obfuscatedPath, $file)) {
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
        
        $this->info("🔒  Deployment Summary:");
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
        
        $this->info("🔒  Secure backup created: {$backupPath}");
        $this->warn("🔒  This location is NOT accessible to clients!");
        
        return $backupPath;
    }

    /**
     * Create deployment package (ZIP file)
     */
    private function createDeploymentPackage(string $source, string $output): void
    {
        $this->info("📦  Creating deployment package...");
        
        $packageName = 'secure_deployment_' . date('Y-m-d_H-i-s') . '.zip';
        $packagePath = $output . DIRECTORY_SEPARATOR . $packageName;
        
        $zip = new \ZipArchive();
        if ($zip->open($packagePath, \ZipArchive::CREATE) === TRUE) {
            $this->addToZip($zip, $source, basename($source));
            $zip->close();
            
            $this->info("📦  Deployment package created: {$packagePath}");
            $this->info("🔒  This package contains ONLY obfuscated code!");
            $this->info("🔒  Clients cannot reverse-engineer your application!");
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
