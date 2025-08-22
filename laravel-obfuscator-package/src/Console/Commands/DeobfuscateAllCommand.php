<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\DeobfuscatorService;
use Illuminate\Support\Facades\File;

class DeobfuscateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'deobfuscate:all {--output-dir= : Output directory for deobfuscated files} {--analyze : Analyze obfuscation level without deobfuscating}';

    /**
     * The console command description.
     */
    protected $description = 'Deobfuscate all PHP files in your Laravel project';

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
        $outputDir = $this->option('output-dir');
        $analyzeOnly = $this->option('analyze');

        try {
            $this->info('Starting deobfuscation of all PHP files...');

            if ($analyzeOnly) {
                $this->info('Analysis mode enabled - no files will be modified');
            }

            if ($outputDir && !is_dir($outputDir)) {
                File::makeDirectory($outputDir, 0755, true);
                $this->info("Created output directory: {$outputDir}");
            }

            // Get the base path of the Laravel project
            $basePath = base_path();
            $this->info("Scanning directory: {$basePath}");

            $results = $this->deobfuscateDirectory($basePath, $outputDir, $analyzeOnly);

            // Display results in a table
            $this->table(
                ['Input', 'Output', 'Status', 'Message'],
                array_map(function ($result) {
                    return [
                        $result['input'],
                        $result['output'] ?? '-',
                        $result['status'] === 'success' ? '✅ Success' : '❌ Error',
                        $result['message'] ?? '-'
                    ];
                }, $results)
            );

            $successCount = count(array_filter($results, fn($r) => $r['status'] === 'success'));
            $totalCount = count($results);

            $this->info("Completed: {$successCount}/{$totalCount} files processed successfully");

            if ($successCount === $totalCount) {
                $this->info('🎉 All files processed successfully!');
                return Command::SUCCESS;
            } else {
                $this->warn('⚠️  Some files failed to process. Check the table above for details.');
                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Deobfuscation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Deobfuscate all PHP files in a directory recursively
     */
    private function deobfuscateDirectory(string $directory, ?string $outputDir, bool $analyzeOnly): array
    {
        $results = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                
                try {
                    if ($analyzeOnly) {
                        $analysis = $this->deobfuscatorService->analyzeObfuscationLevel($filePath);
                        $results[] = [
                            'input' => $filePath,
                            'output' => null,
                            'status' => 'success',
                            'message' => "Analysis: " . ($analysis['is_obfuscated'] ? 'Obfuscated' : 'Not obfuscated') . " ({$analysis['confidence']}% confidence)"
                        ];
                    } else {
                        $outputPath = $outputDir 
                            ? $outputDir . '/' . $file->getFilename()
                            : $this->generateDeobfuscatedPath($filePath);
                        
                        $deobfuscatedFile = $this->deobfuscatorService->deobfuscateFile($filePath, $outputPath);
                        
                        $results[] = [
                            'input' => $filePath,
                            'output' => $deobfuscatedFile,
                            'status' => 'success',
                            'message' => 'Deobfuscated successfully'
                        ];
                    }
                } catch (\Exception $e) {
                    $results[] = [
                        'input' => $filePath,
                        'output' => null,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Generate output path for deobfuscated file
     */
    private function generateDeobfuscatedPath(string $inputPath): string
    {
        $pathInfo = pathinfo($inputPath);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_deobfuscated.' . $pathInfo['extension'];
    }
}
