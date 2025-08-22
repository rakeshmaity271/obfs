<?php

namespace LaravelObfuscator\LaravelObfuscator\Console\Commands;

use Illuminate\Console\Command;
use LaravelObfuscator\LaravelObfuscator\Services\DeobfuscatorService;
use Illuminate\Support\Facades\File;

class DeobfuscateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'obfuscate:deobfuscate 
                            {file : The PHP file to deobfuscate}
                            {--output= : Output file path (optional)}
                            {--analyze : Analyze obfuscation level without deobfuscating}
                            {--batch : Process all PHP files in directory}';

    /**
     * The console command description.
     */
    protected $description = 'Deobfuscate a PHP file or analyze its obfuscation level';

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
        $filePath = $this->argument('file');
        $outputPath = $this->option('output');
        $analyzeOnly = $this->option('analyze');
        $batchMode = $this->option('batch');

        try {
            if ($batchMode) {
                return $this->handleBatchMode($filePath, $analyzeOnly);
            } else {
                return $this->handleSingleFile($filePath, $outputPath, $analyzeOnly);
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Handle single file processing
     */
    private function handleSingleFile(string $filePath, ?string $outputPath, bool $analyzeOnly): int
    {
        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        if ($analyzeOnly) {
            return $this->analyzeFile($filePath);
        }

        $this->info("Deobfuscating file: {$filePath}");
        
        try {
            $outputFile = $this->deobfuscatorService->deobfuscateFile($filePath, $outputPath);
            
            $this->info("File deobfuscated successfully!");
            $this->info("Output file: {$outputFile}");
            
            // Show file size comparison
            $originalSize = File::size($filePath);
            $deobfuscatedSize = File::size($outputFile);
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Original Size', $this->formatBytes($originalSize)],
                    ['Deobfuscated Size', $this->formatBytes($deobfuscatedSize)],
                    ['Size Difference', $this->formatBytes($deobfuscatedSize - $originalSize)]
                ]
            );
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Deobfuscation failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Handle batch mode processing
     */
    private function handleBatchMode(string $directoryPath, bool $analyzeOnly): int
    {
        if (!is_dir($directoryPath)) {
            $this->error("Directory not found: {$directoryPath}");
            return Command::FAILURE;
        }

        $this->info("Processing directory: {$directoryPath}");
        
        if ($analyzeOnly) {
            return $this->analyzeDirectory($directoryPath);
        }

        $this->info("Starting batch deobfuscation...");
        
        $progressBar = $this->output->createProgressBar();
        $results = [];
        
        try {
            $files = File::allFiles($directoryPath);
            $phpFiles = array_filter($files, fn($file) => $file->getExtension() === 'php');
            
            $progressBar->start(count($phpFiles));
            
            foreach ($phpFiles as $file) {
                $filePath = $file->getPathname();
                
                try {
                    $outputPath = $this->deobfuscatorService->deobfuscateFile($filePath);
                    $results[] = [
                        'file' => basename($filePath),
                        'status' => 'success',
                        'output' => $outputPath
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'file' => basename($filePath),
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine(2);
            
            // Show results summary
            $successful = count(array_filter($results, fn($r) => $r['status'] === 'success'));
            $failed = count(array_filter($results, fn($r) => $r['status'] === 'error'));
            
            $this->info("Batch processing completed!");
            $this->info("Successful: {$successful}, Failed: {$failed}");
            
            if ($failed > 0) {
                $this->warn("Failed files:");
                foreach ($results as $result) {
                    if ($result['status'] === 'error') {
                        $this->warn("  - {$result['file']}: {$result['message']}");
                    }
                }
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Batch processing failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Analyze a single file
     */
    private function analyzeFile(string $filePath): int
    {
        $this->info("Analyzing file: {$filePath}");
        
        try {
            $analysis = $this->deobfuscatorService->analyzeObfuscationLevel($filePath);
            
            $this->info("Analysis completed!");
            
            $this->table(
                ['Property', 'Value'],
                [
                    ['Is Obfuscated', $analysis['is_obfuscated'] ? 'Yes' : 'No'],
                    ['Obfuscation Type', $analysis['obfuscation_type']],
                    ['Confidence', $analysis['confidence'] . '%'],
                    ['Techniques Detected', implode(', ', $analysis['techniques_detected'])],
                    ['Estimated Original Size', $this->formatBytes($analysis['estimated_original_size'])]
                ]
            );
            
            if (!empty($analysis['techniques_detected'])) {
                $this->info("Detected obfuscation techniques:");
                foreach ($analysis['techniques_detected'] as $technique) {
                    $this->line("  - {$technique}");
                }
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Analysis failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Analyze a directory
     */
    private function analyzeDirectory(string $directoryPath): int
    {
        $this->info("Analyzing directory: {$directoryPath}");
        
        try {
            $files = File::allFiles($directoryPath);
            $phpFiles = array_filter($files, fn($file) => $file->getExtension() === 'php');
            
            $this->info("Found " . count($phpFiles) . " PHP files to analyze.");
            
            $results = [];
            $progressBar = $this->output->createProgressBar(count($phpFiles));
            
            foreach ($phpFiles as $file) {
                $filePath = $file->getPathname();
                
                try {
                    $analysis = $this->deobfuscatorService->analyzeObfuscationLevel($filePath);
                    $results[] = [
                        'file' => basename($filePath),
                        'is_obfuscated' => $analysis['is_obfuscated'],
                        'type' => $analysis['obfuscation_type'],
                        'confidence' => $analysis['confidence'],
                        'techniques' => implode(', ', $analysis['techniques_detected'])
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'file' => basename($filePath),
                        'is_obfuscated' => false,
                        'type' => 'error',
                        'confidence' => 0,
                        'techniques' => 'Analysis failed: ' . $e->getMessage()
                    ];
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine(2);
            
            // Show summary
            $obfuscatedCount = count(array_filter($results, fn($r) => $r['is_obfuscated']));
            $totalCount = count($results);
            
            $this->info("Analysis Summary:");
            $this->info("Total files: {$totalCount}");
            $this->info("Obfuscated files: {$obfuscatedCount}");
            $this->info("Non-obfuscated files: " . ($totalCount - $obfuscatedCount));
            
            // Show detailed results
            $this->table(
                ['File', 'Obfuscated', 'Type', 'Confidence', 'Techniques'],
                array_map(function ($result) {
                    return [
                        $result['file'],
                        $result['is_obfuscated'] ? 'Yes' : 'No',
                        $result['type'],
                        $result['confidence'] . '%',
                        $result['techniques']
                    ];
                }, $results)
            );
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Directory analysis failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
