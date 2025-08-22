<?php

namespace LaravelObfuscator\LaravelObfuscator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\JsonResponse;

class ObfuscatorApiController extends Controller
{
    protected $obfuscatorService;

    public function __construct(ObfuscatorService $obfuscatorService)
    {
        $this->obfuscatorService = $obfuscatorService;
        $this->middleware('auth:sanctum');
        $this->middleware('throttle:60,1'); // 60 requests per minute
    }

    /**
     * Obfuscate a single file via API
     */
    public function obfuscate(Request $request): JsonResponse
    {
        // Check rate limiting
        if (RateLimiter::tooManyAttempts('api-obfuscate:' . $request->user()->id, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.'
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'file_content' => 'required|string|max:10485760', // 10MB max
            'filename' => 'required|string|max:255',
            'create_backup' => 'boolean',
            'obfuscation_level' => 'in:basic,advanced,enterprise',
            'options' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'obf_api_');
            file_put_contents($tempFile, $request->input('file_content'));

            // Create backup if requested
            $backupPath = null;
            if ($request->boolean('create_backup')) {
                $backupPath = $this->obfuscatorService->createBackup(
                    $tempFile, 
                    $request->input('filename')
                );
            }

            // Obfuscate the file
            $obfuscatedPath = $this->obfuscatorService->obfuscateFile(
                $tempFile,
                null,
                $request->get('obfuscation_level', 'basic'),
                $request->get('options', [])
            );

            // Read obfuscated content
            $obfuscatedContent = file_get_contents($obfuscatedPath);

            // Clean up temporary files
            unlink($tempFile);
            unlink($obfuscatedPath);

            // Increment rate limiter
            RateLimiter::hit('api-obfuscate:' . $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'File obfuscated successfully',
                'data' => [
                    'filename' => $request->input('filename'),
                    'backup_path' => $backupPath,
                    'obfuscated_content' => $obfuscatedContent,
                    'obfuscation_level' => $request->get('obfuscation_level', 'basic'),
                    'file_size' => strlen($obfuscatedContent),
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
            if (isset($obfuscatedPath) && file_exists($obfuscatedPath)) {
                unlink($obfuscatedPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Obfuscation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batch obfuscation via API
     */
    public function batchObfuscate(Request $request): JsonResponse
    {
        // Check rate limiting for batch operations
        if (RateLimiter::tooManyAttempts('api-batch:' . $request->user()->id, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Batch rate limit exceeded. Please try again later.'
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:10', // Max 10 files per batch
            'files.*.content' => 'required|string|max:10485760',
            'files.*.filename' => 'required|string|max:255',
            'create_backup' => 'boolean',
            'obfuscation_level' => 'in:basic,advanced,enterprise',
            'options' => 'array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $tempFiles = [];

        try {
            foreach ($request->input('files') as $index => $fileData) {
                // Create temporary file
                $tempFile = tempnam(sys_get_temp_dir(), 'obf_batch_' . $index . '_');
                file_put_contents($tempFile, $fileData['content']);
                $tempFiles[] = $tempFile;

                try {
                    // Create backup if requested
                    $backupPath = null;
                    if ($request->boolean('create_backup')) {
                        $backupPath = $this->obfuscatorService->createBackup(
                            $tempFile, 
                            $fileData['filename']
                        );
                    }

                    // Obfuscate the file
                    $obfuscatedPath = $this->obfuscatorService->obfuscateFile(
                        $tempFile,
                        null,
                        $request->get('obfuscation_level', 'basic'),
                        $request->get('options', [])
                    );

                    // Read obfuscated content
                    $obfuscatedContent = file_get_contents($obfuscatedPath);

                    $results[] = [
                        'filename' => $fileData['filename'],
                        'status' => 'success',
                        'backup_path' => $backupPath,
                        'obfuscated_content' => $obfuscatedContent,
                        'file_size' => strlen($obfuscatedContent)
                    ];

                    // Clean up obfuscated file
                    unlink($obfuscatedPath);

                } catch (\Exception $e) {
                    $results[] = [
                        'filename' => $fileData['filename'],
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }

            // Increment rate limiter
            RateLimiter::hit('api-batch:' . $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Batch processing completed',
                'data' => [
                    'total_files' => count($request->input('files')),
                    'successful' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
                    'failed' => count(array_filter($results, fn($r) => $r['status'] === 'error')),
                    'results' => $results,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } finally {
            // Clean up all temporary files
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        }
    }

    /**
     * Get obfuscation status and statistics
     */
    public function status(): JsonResponse
    {
        $stats = $this->getObfuscationStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get available obfuscation options
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'obfuscation_levels' => [
                    'basic' => 'Standard obfuscation with base64 encoding',
                    'advanced' => 'Enhanced obfuscation with variable randomization',
                    'enterprise' => 'Maximum obfuscation with all techniques'
                ],
                'available_options' => [
                    'randomize_variables' => 'Randomize variable names',
                    'encrypt_strings' => 'Encrypt string literals',
                    'control_flow_obfuscation' => 'Obfuscate control flow',
                    'dead_code_injection' => 'Inject meaningless code',
                    'anti_debugging' => 'Add anti-debugging measures'
                ],
                'rate_limits' => [
                    'single_file' => '60 requests per minute',
                    'batch_processing' => '5 batches per minute',
                    'max_file_size' => '10MB per file',
                    'max_batch_size' => '10 files per batch'
                ]
            ]
        ]);
    }

    /**
     * Get obfuscation statistics
     */
    protected function getObfuscationStats(): array
    {
        $backupDir = config('laravel-obfuscator.backup_directory', 'storage/app/obfuscator_backups');
        $outputDir = config('laravel-obfuscator.output_directory', 'storage/app/obfuscated');
        
        $backupCount = is_dir($backupDir) ? count(glob($backupDir . '/*.php')) : 0;
        $outputCount = is_dir($outputDir) ? count(glob($outputDir . '/*.php')) : 0;
        
        return [
            'total_backups' => $backupCount,
            'total_obfuscated' => $outputCount,
            'storage_used' => $this->formatBytes($this->getDirectorySize($backupDir) + $this->getDirectorySize($outputDir)),
            'last_activity' => $this->getLastActivityTime(),
            'api_usage' => [
                'single_file_remaining' => RateLimiter::remaining('api-obfuscate:' . auth()->id(), 60),
                'batch_remaining' => RateLimiter::remaining('api-batch:' . auth()->id(), 5)
            ]
        ];
    }

    /**
     * Get directory size in bytes
     */
    protected function getDirectorySize(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $size = 0;
        $files = glob($dir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $size += filesize($file);
            } elseif (is_dir($file)) {
                $size += $this->getDirectorySize($file);
            }
        }

        return $size;
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Get last activity time
     */
    protected function getLastActivityTime(): ?string
    {
        $backupDir = config('laravel-obfuscator.backup_directory', 'storage/app/obfuscator_backups');
        $outputDir = config('laravel-obfuscator.output_directory', 'storage/app/obfuscated');
        
        $lastBackup = is_dir($backupDir) ? $this->getLastModifiedTime($backupDir) : 0;
        $lastOutput = is_dir($outputDir) ? $this->getLastModifiedTime($outputDir) : 0;
        
        $lastActivity = max($lastBackup, $lastOutput);
        
        return $lastActivity > 0 ? date('c', $lastActivity) : null;
    }

    /**
     * Get last modified time for directory
     */
    protected function getLastModifiedTime(string $dir): int
    {
        $files = glob($dir . '/*');
        $lastModified = 0;
        
        foreach ($files as $file) {
            $modified = filemtime($file);
            if ($modified > $lastModified) {
                $lastModified = $modified;
            }
        }
        
        return $lastModified;
    }
}
