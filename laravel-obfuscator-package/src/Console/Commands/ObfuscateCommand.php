<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;

class ObfuscateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:file 
                            {input : Input file path to obfuscate}
                            {--output= : Output file path (optional)}
                            {--backup : Create backup of original file}
                            {--replace : Replace original file (DANGEROUS!)}';

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
            
            // Move obfuscated file to replace original
            if (rename($output, $input)) {
                $this->info('✅ Original file replaced successfully!');
                $this->warn('⚠️  Original file is now obfuscated!');
                $this->info('💾 Backup created for safety.');
            } else {
                $this->error('❌ Failed to replace original file!');
                return Command::FAILURE;
            }
        } else {
            $this->info('✅ File obfuscated successfully!');
        }
        
        return Command::SUCCESS;
    }
}
