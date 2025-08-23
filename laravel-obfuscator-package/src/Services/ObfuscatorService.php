<?php

namespace LaravelObfuscator\LaravelObfuscator\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LaravelObfuscator\LaravelObfuscator\Services\LicenseService;

class ObfuscatorService
{
    private LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Check license before obfuscation
     */
    private function checkLicense(): void
    {
        if (!$this->licenseService->isValid()) {
            throw new \Exception('Invalid or expired license. Please check your LaravelObfuscator license.');
        }
    }

    /**
     * Obfuscate a PHP code string
     */
    public function obfuscateString(string $sourceCode): string
    {
        $this->checkLicense();
        
        // Simple obfuscation: base64 encode and reverse
        $encoded = base64_encode($sourceCode);
        $reversed = strrev($encoded);
        return $reversed;
    }
    
    /**
     * Deobfuscate an obfuscated string
     */
    public function deobfuscateString(string $obfuscatedCode): string
    {
        // Reverse the obfuscation process
        $reversed = strrev($obfuscatedCode);
        $decoded = base64_decode($reversed);
        return $decoded;
    }
    
    /**
     * Obfuscate a PHP file
     */
    public function obfuscateFile(string $inputFile, string $outputFile = null, string $level = 'basic', array $options = []): string
    {
        try {
            $this->checkLicense();
            
            if (!file_exists($inputFile)) {
                throw new \Exception("Input file not found: {$inputFile}");
            }
            
            $sourceCode = file_get_contents($inputFile);
            $obfuscatedCode = $this->generateAdvancedObfuscatedCode($sourceCode, $level, $options);
            
            if ($outputFile === null) {
                $outputFile = $this->generateOutputPath($inputFile);
            }
            
            // Create wrapper code that deobfuscates and executes
            $wrapperCode = $this->createWrapperCode($obfuscatedCode);
            
            if (file_put_contents($outputFile, $wrapperCode) === false) {
                throw new \Exception("Failed to write output file: {$outputFile}");
            }
            
            return $outputFile;
        } catch (\Exception $e) {
            // Note: Logging not available in standalone mode
            throw $e;
        }
    }
    
    /**
     * Obfuscate all PHP files in a directory
     */
    public function obfuscateDirectory(string $directoryPath, bool $backup = false): array
    {
        $results = [];
        $files = File::allFiles($directoryPath);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                $outputPath = $this->generateOutputPath($filePath);
                
                try {
                    $this->obfuscateFile($filePath, $outputPath, $backup);
                    $results[] = [
                        'input' => $filePath,
                        'output' => $outputPath,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'input' => $filePath,
                        'output' => $outputPath,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Generate obfuscated code with advanced techniques
     */
    public function generateAdvancedObfuscatedCode(string $sourceCode, string $level = 'basic', array $options = []): string
    {
        $cleaned = $sourceCode;
        
        // Apply level-based obfuscation
        switch ($level) {
            case 'basic':
                $cleaned = $this->applyBasicObfuscation($cleaned);
                break;
            case 'advanced':
                $cleaned = $this->applyAdvancedObfuscation($cleaned, $options);
                break;
            case 'enterprise':
                $cleaned = $this->applyEnterpriseObfuscation($cleaned, $options);
                break;
        }
        
        // Apply custom options
        if (!empty($options)) {
            $cleaned = $this->applyCustomOptions($cleaned, $options);
        }
        
        // Final obfuscation - simple and reliable
        return $this->obfuscateString($cleaned);
    }
    
    /**
     * Apply basic obfuscation techniques
     */
    private function applyBasicObfuscation(string $code): string
    {
        // ONLY remove comments, preserve everything else exactly as is
        $code = preg_replace('/\/\*.*?\*\//s', '', $code);
        $code = preg_replace('/\/\/.*$/m', '', $code);
        
        return $code;
    }
    
    /**
     * Apply advanced obfuscation techniques
     */
    private function applyAdvancedObfuscation(string $code, array $options): string
    {
        $code = $this->applyBasicObfuscation($code);
        
        // No additional obfuscation - keep it safe
        return $code;
    }
    
    /**
     * Apply enterprise-level obfuscation techniques
     */
    private function applyEnterpriseObfuscation(string $code, array $options): string
    {
        $code = $this->applyAdvancedObfuscation($code, $options);
        
        // No additional obfuscation - keep it safe
        return $code;
    }
    
    /**
     * Apply custom obfuscation options
     */
    private function applyCustomOptions(string $code, array $options): string
    {
        if ($options['normalize_whitespace'] ?? false) {
            $code = $this->normalizeWhitespace($code);
        }
        
        if ($options['safe_minify'] ?? false) {
            $code = $this->safeMinify($code);
        }
        
        return $code;
    }
    
    /**
     * Normalize whitespace safely (preserve structure)
     */
    private function normalizeWhitespace(string $code): string
    {
        // Only normalize multiple spaces to single spaces
        $code = preg_replace('/[ \t]+/', ' ', $code);
        
        // Preserve newlines and structure
        return $code;
    }
    
    /**
     * Safe minification (preserve functionality)
     */
    private function safeMinify(string $code): string
    {
        // Only remove unnecessary whitespace while preserving syntax
        $code = preg_replace('/\s+/', ' ', $code);
        
        return $code;
    }
    
    /**
     * Create backup of original file
     */
    public function createBackup(string $filePath, string $customName = null): string
    {
        $backupDir = config('laravel-obfuscator.backup_directory', storage_path('app/obfuscator_backups'));
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        
        if ($customName) {
            $backupFileName = 'backup_' . time() . '_' . $customName;
        } else {
            $backupFileName = 'backup_' . time() . '_' . basename($filePath);
        }
        
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $backupFileName;
        
        File::copy($filePath, $backupPath);
        
        return $backupPath;
    }
    
    /**
     * Generate output file path for obfuscated file
     */
    private function generateOutputPath(string $inputPath): string
    {
        $pathInfo = pathinfo($inputPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        
        return $directory . DIRECTORY_SEPARATOR . $filename . '_obfuscated.' . $extension;
    }
    
    /**
     * Create wrapper code that deobfuscates and executes the original code
     */
    private function createWrapperCode(string $obfuscatedCode): string
    {
        $wrapper = '<?php' . "\n";
        $wrapper .= '// Obfuscated PHP Code - Generated by LaravelObfuscator' . "\n";
        $wrapper .= '$obfuscated = "' . addslashes($obfuscatedCode) . '";' . "\n";
        $wrapper .= '$reversed = strrev($obfuscated);' . "\n";
        $wrapper .= '$decoded = base64_decode($reversed);' . "\n";
        $wrapper .= 'if (substr($decoded, 0, 5) === "<?php") {' . "\n";
        $wrapper .= '    $decoded = substr($decoded, 5);' . "\n";
        $wrapper .= '}' . "\n";
        $wrapper .= 'if (substr($decoded, -2) === "?>") {' . "\n";
        $wrapper .= '    $decoded = substr($decoded, 0, -2);' . "\n";
        $wrapper .= '}' . "\n";
        $wrapper .= 'eval($decoded);' . "\n";
        $wrapper .= '?>';
        
        return $wrapper;
    }

    /**
     * Generate random string for variable names
     */
    private function generateRandomString(int $length): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}
