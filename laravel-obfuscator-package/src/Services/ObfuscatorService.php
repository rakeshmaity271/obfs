<?php

namespace LaravelObfuscator\LaravelObfuscator\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ObfuscatorService
{
    /**
     * Obfuscate a PHP code string
     */
    public function obfuscateString(string $sourceCode): string
    {
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
            if (!File::exists($inputFile)) {
                throw new \Exception("Input file not found: {$inputFile}");
            }
            
            $sourceCode = File::get($inputFile);
            $obfuscatedCode = $this->generateAdvancedObfuscatedCode($sourceCode, $level, $options);
            
            if ($outputFile === null) {
                $outputFile = $this->generateOutputPath($inputFile);
            }
            
            // Create wrapper code that deobfuscates and executes
            $wrapperCode = $this->createWrapperCode($obfuscatedCode);
            
            if (File::put($outputFile, $wrapperCode) === false) {
                throw new \Exception("Failed to write output file: {$outputFile}");
            }
            
            return $outputFile;
        } catch (\Exception $e) {
            \Log::error('Obfuscation error: ' . $e->getMessage());
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
        
        // Final obfuscation
        return $this->obfuscateString($cleaned);
    }
    
    /**
     * Apply basic obfuscation techniques
     */
    private function applyBasicObfuscation(string $code): string
    {
        // Remove comments and extra whitespace
        $code = preg_replace('/\/\*.*?\*\//s', '', $code);
        $code = preg_replace('/\/\/.*$/m', '', $code);
        $code = preg_replace('/\s+/', ' ', $code);
        
        return $code;
    }
    
    /**
     * Apply advanced obfuscation techniques
     */
    private function applyAdvancedObfuscation(string $code, array $options): string
    {
        $code = $this->applyBasicObfuscation($code);
        
        // Variable name randomization
        if (empty($options) || $options['randomize_variables'] ?? true) {
            $code = $this->randomizeVariableNames($code);
        }
        
        // String encryption
        if (empty($options) || $options['encrypt_strings'] ?? true) {
            $code = $this->encryptStrings($code);
        }
        
        return $code;
    }
    
    /**
     * Apply enterprise-level obfuscation techniques
     */
    private function applyEnterpriseObfuscation(string $code, array $options): string
    {
        $code = $this->applyAdvancedObfuscation($code, $options);
        
        // Control flow obfuscation
        if (empty($options) || $options['control_flow_obfuscation'] ?? true) {
            $code = $this->obfuscateControlFlow($code);
        }
        
        // Dead code injection
        if (empty($options) || $options['dead_code_injection'] ?? true) {
            $code = $this->injectDeadCode($code);
        }
        
        // Anti-debugging measures
        if (empty($options) || $options['anti_debugging'] ?? true) {
            $code = $this->addAntiDebugging($code);
        }
        
        return $code;
    }
    
    /**
     * Apply custom obfuscation options
     */
    private function applyCustomOptions(string $code, array $options): string
    {
        if ($options['randomize_variables'] ?? false) {
            $code = $this->randomizeVariableNames($code);
        }
        
        if ($options['encrypt_strings'] ?? false) {
            $code = $this->encryptStrings($code);
        }
        
        if ($options['control_flow_obfuscation'] ?? false) {
            $code = $this->obfuscateControlFlow($code);
        }
        
        if ($options['dead_code_injection'] ?? false) {
            $code = $this->injectDeadCode($code);
        }
        
        if ($options['anti_debugging'] ?? false) {
            $code = $this->addAntiDebugging($code);
        }
        
        return $code;
    }
    
    /**
     * Randomize variable names
     */
    private function randomizeVariableNames(string $code): string
    {
        // Simple variable name randomization (can be enhanced)
        $variables = [];
        preg_match_all('/\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/', $code, $matches);
        
        foreach ($matches[1] as $var) {
            if (!isset($variables[$var])) {
                $variables[$var] = '_' . bin2hex(random_bytes(4));
            }
        }
        
        foreach ($variables as $original => $random) {
            $code = preg_replace('/\$' . preg_quote($original, '/') . '\b/', '$' . $random, $code);
        }
        
        return $code;
    }
    
    /**
     * Encrypt string literals
     */
    private function encryptStrings(string $code): string
    {
        // Simple string encryption (can be enhanced)
        return preg_replace_callback('/"([^"]*)"/', function($matches) {
            $encrypted = base64_encode($matches[1]);
            return '"' . $encrypted . '"';
        }, $code);
    }
    
    /**
     * Obfuscate control flow
     */
    private function obfuscateControlFlow(string $code): string
    {
        // Add dummy if statements to confuse analysis
        $code = 'if(true){' . $code . '}';
        return $code;
    }
    
    /**
     * Inject meaningless code
     */
    private function injectDeadCode(string $code): string
    {
        $deadCode = [
            '$dummy = "dead_code";',
            'if(false){ $never = "executed"; }',
            'for($i=0;$i<0;$i++){ $loop = "never"; }'
        ];
        
        $randomDeadCode = $deadCode[array_rand($deadCode)];
        return $code . "\n" . $randomDeadCode;
    }
    
    /**
     * Add anti-debugging measures
     */
    private function addAntiDebugging(string $code): string
    {
        $antiDebug = [
            'if(function_exists("xdebug_get_trace")){ return; }',
            'if(extension_loaded("xdebug")){ return; }'
        ];
        
        $randomAntiDebug = $antiDebug[array_rand($antiDebug)];
        return $randomAntiDebug . "\n" . $code;
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
}
