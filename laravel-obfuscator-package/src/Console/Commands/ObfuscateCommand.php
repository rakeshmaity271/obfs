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
                            {--backup : Create backup of original file}';

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

        if (!$output) {
            $pathInfo = pathinfo($input);
            $output = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_obfuscated.' . $pathInfo['extension'];
        }

        $this->info("Obfuscating: {$input}");
        $this->info("Output: {$output}");

        if ($backup) {
            $this->info("Creating backup...");
        }

        $obfuscator->obfuscateFile($input, $output, $backup);

        $this->info('File obfuscated successfully!');
        return Command::SUCCESS;
    }
}
