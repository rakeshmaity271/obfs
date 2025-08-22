<?php

namespace LaravelObfuscator\LaravelObfuscator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ObfuscatorController extends Controller
{
    protected $obfuscatorService;

    public function __construct(ObfuscatorService $obfuscatorService)
    {
        $this->obfuscatorService = $obfuscatorService;
    }

    /**
     * Display the main dashboard
     */
    public function dashboard()
    {
        $backups = $this->getBackupFiles();
        $stats = $this->getObfuscationStats();
        
        return view('laravel-obfuscator::dashboard', compact('backups', 'stats'));
    }

    /**
     * Handle file upload and obfuscation
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'php_file' => 'required|file|mimes:php|max:10240', // 10MB max
            'create_backup' => 'boolean',
            'obfuscation_level' => 'in:basic,advanced,enterprise'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('php_file');
            $originalName = $file->getClientOriginalName();
            $tempPath = $file->getRealPath();
            
            // Create backup if requested
            $backupPath = null;
            if ($request->boolean('create_backup')) {
                $backupPath = $this->obfuscatorService->createBackup($tempPath, $originalName);
            }

            // Obfuscate the file
            $obfuscatedPath = $this->obfuscatorService->obfuscateFile(
                $tempPath, 
                null, 
                $request->get('obfuscation_level', 'basic')
            );

            // Generate download link
            $downloadToken = $this->generateDownloadToken($obfuscatedPath);

            return response()->json([
                'success' => true,
                'message' => 'File obfuscated successfully!',
                'data' => [
                    'original_name' => $originalName,
                    'backup_path' => $backupPath,
                    'obfuscated_path' => $obfuscatedPath,
                    'download_token' => $downloadToken,
                    'file_size' => filesize($obfuscatedPath),
                    'obfuscation_level' => $request->get('obfuscation_level', 'basic')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Obfuscation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download obfuscated file
     */
    public function download($token)
    {
        $filePath = $this->resolveDownloadToken($token);
        
        if (!$filePath || !file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath, basename($filePath));
    }

    /**
     * Batch obfuscation
     */
    public function batchUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'php_files.*' => 'required|file|mimes:php|max:10240',
            'create_backup' => 'boolean',
            'obfuscation_level' => 'in:basic,advanced,enterprise'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $results = [];
        $files = $request->file('php_files');

        foreach ($files as $file) {
            try {
                $originalName = $file->getClientOriginalName();
                $tempPath = $file->getRealPath();
                
                $obfuscatedPath = $this->obfuscatorService->obfuscateFile(
                    $tempPath, 
                    null, 
                    $request->get('obfuscation_level', 'basic')
                );

                $results[] = [
                    'file' => $originalName,
                    'status' => 'success',
                    'obfuscated_path' => $obfuscatedPath,
                    'download_token' => $this->generateDownloadToken($obfuscatedPath)
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'file' => $originalName,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Batch processing completed',
            'results' => $results
        ]);
    }

    /**
     * Get obfuscation statistics
     */
    public function stats()
    {
        $stats = $this->getObfuscationStats();
        
        return response()->json($stats);
    }

    /**
     * Get backup files list
     */
    protected function getBackupFiles()
    {
        $backupDir = config('laravel-obfuscator.backup_directory', 'storage/app/obfuscator_backups');
        
        if (!is_dir($backupDir)) {
            return [];
        }

        $files = glob($backupDir . '/*.php');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => filesize($file),
                'created' => filemtime($file),
                'path' => $file
            ];
        }

        return $backups;
    }

    /**
     * Get obfuscation statistics
     */
    protected function getObfuscationStats()
    {
        $backupDir = config('laravel-obfuscator.backup_directory', 'storage/app/obfuscator_backups');
        $outputDir = config('laravel-obfuscator.output_directory', 'storage/app/obfuscated');
        
        $backupCount = is_dir($backupDir) ? count(glob($backupDir . '/*.php')) : 0;
        $outputCount = is_dir($outputDir) ? count(glob($outputDir . '/*.php')) : 0;
        
        return [
            'total_backups' => $backupCount,
            'total_obfuscated' => $outputCount,
            'storage_used' => $this->formatBytes($this->getDirectorySize($backupDir) + $this->getDirectorySize($outputDir)),
            'last_activity' => $this->getLastActivityTime()
        ];
    }

    /**
     * Generate download token for file
     */
    protected function generateDownloadToken($filePath)
    {
        $token = md5($filePath . time() . uniqid());
        
        // Store token in cache for 1 hour
        cache()->put('download_token_' . $token, $filePath, 3600);
        
        return $token;
    }

    /**
     * Resolve download token to file path
     */
    protected function resolveDownloadToken($token)
    {
        return cache()->get('download_token_' . $token);
    }

    /**
     * Get directory size in bytes
     */
    protected function getDirectorySize($dir)
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
    protected function formatBytes($bytes, $precision = 2)
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
    protected function getLastActivityTime()
    {
        $backupDir = config('laravel-obfuscator.backup_directory', 'storage/app/obfuscator_backups');
        $outputDir = config('laravel-obfuscator.output_directory', 'storage/app/obfuscated');
        
        $lastBackup = is_dir($backupDir) ? $this->getLastModifiedTime($backupDir) : 0;
        $lastOutput = is_dir($outputDir) ? $this->getLastModifiedTime($outputDir) : 0;
        
        return max($lastBackup, $lastOutput);
    }

    /**
     * Get last modified time for directory
     */
    protected function getLastModifiedTime($dir)
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
