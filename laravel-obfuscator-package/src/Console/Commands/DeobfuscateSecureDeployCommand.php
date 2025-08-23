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
                            {source : Source directory or file to deploy}
                            {--output= : Output directory for deployment package}
                            {--exclude=* : Files/directories to exclude from deobfuscation}
                            {--create-package : Create a deployment package (ZIP file)}';

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
        $source = $this->argument('source');
        $output = $this->option('output');
        $exclude = $this->option('exclude');
        $createPackage = $this->option('create-package');

        if (!file_exists($source)) {
            $this->error("Source not found: {$source}");
            return Command::FAILURE;
        }

        $this->warn('🔓  SECURE DEOBFUSCATION DEPLOYMENT PACKAGE CREATION');
        $this->warn('🔓  This will create a client-ready package with readable, maintainable code!');
        $this->warn('🔓  Original obfuscated files will be moved to SECURE backup location.');
        
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
        
        $successCount = 0;
        $totalCount = count($phpFiles);
        
        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();
        
        foreach ($phpFiles as $file) {
            try {
                $progressBar->advance();
                
                // Deobfuscate to temporary file first
                $tempOutput = $this->generateTempOutputPath($file);
                $deobfuscatedPath = $this->deobfuscatorService->deobfuscateFile($file, $tempOutput);
                
                // Replace original with deobfuscated
                if (rename($tempOutput, $file)) {
                    $successCount++;
                } else {
                    $this->warn("⚠️  Failed to replace: {$file}");
                }
            } catch (\Exception $e) {
                $this->warn("⚠️  Failed to deobfuscate: {$file} - {$e->getMessage()}");
            }
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("🔓  Successfully deployed {$successCount}/{$totalCount} files securely!");
        
        if ($successCount === $totalCount) {
            $this->info('🔓  SECURE DEOBFUSCATION DEPLOYMENT COMPLETE!');
            $this->info('🔓  Client can now read and maintain all code!');
            $this->info('🔓  Only deobfuscated versions are accessible!');
        } else {
            $this->warn('⚠️  Some files failed to deploy securely. Check the warnings above.');
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
     * Generate temporary output path for deobfuscated file
     */
    private function generateTempOutputPath(string $filePath): string
    {
        $pathInfo = pathinfo($filePath);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_temp_deobfuscated.' . $pathInfo['extension'];
    }

    /**
     * Create secure backup (client cannot access)
     */
    private function createSecureBackup(string $path): string
    {
        $secureBackupDir = storage_path('app/secure_deobfuscation_backups/' . date('Y-m-d_H-i-s'));
        
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
        $this->info("📦  Creating deobfuscation deployment package...");
        
        $packageName = 'deobfuscated_deployment_' . date('Y-m-d_H-i-s') . '.zip';
        $packagePath = $output . DIRECTORY_SEPARATOR . $packageName;
        
        $zip = new \ZipArchive();
        if ($zip->open($packagePath, \ZipArchive::CREATE) === TRUE) {
            $this->addToZip($zip, $source, basename($source));
            $zip->close();
            
            $this->info("📦  Deobfuscation deployment package created: {$packagePath}");
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
