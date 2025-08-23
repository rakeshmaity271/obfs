<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use Illuminate\Support\Facades\File;

class ObfuscateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:file 
                            {input : Input file path to obfuscate}
                            {--output= : Output file path (optional)}
                            {--backup : Create backup of original file}
                            {--replace : Replace original file (DANGEROUS!)}
                            {--secure-deploy : Secure deployment mode - replace original with obfuscated, move original to secure backup}';

    /**
     * The console command description.
     */
    protected $description = 'Obfuscate a specific PHP file using LaravelObfuscator';

    /**
     * Execute the console command.
     */
    public function handle(ObfuscatorService $obfuscator): int
    {
        $input = $this->argument('input');
        $output = $this->option('output');
        $backup = $this->option('backup');

        try {
            return $this->obfuscateFile($obfuscator, $input, $output, $backup);
        } catch (\Exception $e) {
            $this->error('Obfuscation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Obfuscate a single file
     */
    private function obfuscateFile(ObfuscatorService $obfuscator, string $input, ?string $output, bool $backup): int
    {
        if (!file_exists($input)) {
            $this->error("Input file not found: {$input}");
            return Command::FAILURE;
        }

        $replace = $this->option('replace');
        $secureDeploy = $this->option('secure-deploy');
        
        // Safety check for replace option
        if ($replace) {
            $this->warn('⚠️  DANGER: You are about to REPLACE the original file!');
            $this->warn('⚠️  This action cannot be undone!');
            
            if (!$this->confirm('Are you absolutely sure you want to replace the original file?')) {
                $this->info('Operation cancelled. Original file preserved.');
                return Command::SUCCESS;
            }
            
            // Force backup when replacing
            $backup = true;
            $this->warn('⚠️  Forcing backup creation for safety...');
        }

        // Secure deployment mode - TRUE SECURITY
        if ($secureDeploy) {
            $this->warn('🔒  SECURE DEPLOYMENT MODE ACTIVATED!');
            $this->warn('🔒  This will make your code truly secure for client deployment.');
            $this->warn('🔒  Original files will be moved to SECURE backup location.');
            $this->warn('🔒  Only obfuscated files will remain accessible.');
            
            if (!$this->confirm('Are you ready to deploy securely? This cannot be undone!')) {
                $this->info('Secure deployment cancelled. Original files preserved.');
                return Command::SUCCESS;
            }
            
            // Force backup and replace for secure deployment
            $backup = true;
            $replace = true;
            $this->info('🔒  Proceeding with secure deployment...');
        }

        if (!$output) {
            if ($replace) {
                // Create temporary file first
                $pathInfo = pathinfo($input);
                $tempOutput = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_temp_obfuscated.' . $pathInfo['extension'];
                $output = $tempOutput;
            } else {
                // Normal behavior
                $pathInfo = pathinfo($input);
                $output = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_obfuscated.' . $pathInfo['extension'];
            }
        }

        $this->info("Obfuscating: {$input}");
        $this->info("Output: {$output}");

        if ($backup) {
            $this->info("Creating backup...");
        }

        $obfuscator->obfuscateFile($input, $output, $backup);

        // If replacing, move the obfuscated file to replace original
        if ($replace) {
            $this->warn('⚠️  Replacing original file with obfuscated version...');
            
            // For secure deployment, create secure backup first
            if ($secureDeploy) {
                $this->info('🔒  Creating secure backup of original file...');
                $secureBackupPath = $this->createSecureBackup($input);
                $this->info("🔒  Original file securely backed up to: {$secureBackupPath}");
            }
            
            // Move obfuscated file to replace original
            if (rename($output, $input)) {
                $this->info('✅ Original file replaced successfully!');
                $this->warn('⚠️  Original file is now obfuscated!');
                
                if ($secureDeploy) {
                    $this->info('🔒  SECURE DEPLOYMENT COMPLETE!');
                    $this->info('🔒  Client can no longer access original source code!');
                    $this->info('🔒  Only obfuscated version is accessible!');
                } else {
                    $this->info('💾 Backup created for safety.');
                }
            } else {
                $this->error('❌ Failed to replace original file!');
                return Command::FAILURE;
            }
        } else {
            $this->info('✅ File obfuscated successfully!');
        }
        
        return Command::SUCCESS;
    }

    /**
     * Create secure backup for deployment (client cannot access)
     */
    private function createSecureBackup(string $filePath): string
    {
        $secureBackupDir = storage_path('app/secure_deployment_backups/' . date('Y-m-d_H-i-s'));
        
        if (!File::exists($secureBackupDir)) {
            File::makeDirectory($secureBackupDir, 0750, true);
        }
        
        $backupFileName = basename($filePath);
        $secureBackupPath = $secureBackupDir . DIRECTORY_SEPARATOR . $backupFileName;
        
        if (File::copy($filePath, $secureBackupPath)) {
            $this->info("🔒  Original file moved to secure backup: {$secureBackupPath}");
            $this->warn("🔒  This location is NOT accessible to clients!");
            return $secureBackupPath;
        } else {
            throw new \Exception("Failed to create secure backup: {$secureBackupPath}");
        }
    }
}
