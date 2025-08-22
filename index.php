<?php

/**
 * PHP Code Obfuscator Demo
 * This file demonstrates basic PHP code obfuscation techniques
 * Note: This standalone obfuscator works without requiring Laravel framework
 */

class SimpleObfuscator
{
    /**
     * Basic string obfuscation by encoding and scrambling
     */
    public function obfuscateString($sourceCode)
    {
        // Simple obfuscation: base64 encode and reverse
        $encoded = base64_encode($sourceCode);
        $reversed = strrev($encoded);
        return $reversed;
    }
    
    /**
     * Deobfuscate the obfuscated string
     */
    public function deobfuscateString($obfuscatedCode)
    {
        // Reverse the obfuscation process
        $reversed = strrev($obfuscatedCode);
        $decoded = base64_decode($reversed);
        return $decoded;
    }
    
    /**
     * Deobfuscate a PHP file
     */
    public function deobfuscateFile($inputFile, $outputFile = null)
    {
        try {
            if (!file_exists($inputFile)) {
                throw new Exception("Input file not found: {$inputFile}");
            }
            
            $obfuscatedCode = file_get_contents($inputFile);
            
            // Try to extract obfuscated content from wrapper
            if (preg_match('/\$obfuscated = "([^"]+)";/', $obfuscatedCode, $matches)) {
                $obfuscatedString = $matches[1];
                $deobfuscatedCode = $this->deobfuscateString($obfuscatedString);
            } else {
                // Try direct deobfuscation if it's just the encoded string
                $deobfuscatedCode = $this->deobfuscateString($obfuscatedCode);
            }
            
            if ($outputFile === null) {
                $pathInfo = pathinfo($inputFile);
                $outputFile = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_deobfuscated.' . $pathInfo['extension'];
            }
            
            if (file_put_contents($outputFile, $deobfuscatedCode) === false) {
                throw new Exception("Failed to write deobfuscated file: {$outputFile}");
            }
            
            return $outputFile;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Analyze obfuscation level of a file
     */
    public function analyzeObfuscationLevel($filePath)
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }
            
            $content = file_get_contents($filePath);
            
            // Check for common obfuscation patterns
            $patterns = [
                'base64' => '/base64_decode/',
                'strrev' => '/strrev/',
                'eval' => '/eval\s*\(/',
                'obfuscated' => '/\$obfuscated/',
                'encoded' => '/[A-Za-z0-9+\/]{20,}={0,2}/' // Base64-like patterns
            ];
            
            $score = 0;
            $found = [];
            
            foreach ($patterns as $type => $pattern) {
                if (preg_match($pattern, $content)) {
                    $score += 20;
                    $found[] = $type;
                }
            }
            
            $isObfuscated = $score >= 40;
            $confidence = min(100, $score);
            
            return [
                'is_obfuscated' => $isObfuscated,
                'confidence' => $confidence,
                'patterns_found' => $found,
                'score' => $score
            ];
        } catch (Exception $e) {
            return [
                'is_obfuscated' => false,
                'confidence' => 0,
                'patterns_found' => [],
                'score' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obfuscate a PHP file with basic techniques
     */
    public function obfuscateFile($inputFile, $outputFile)
    {
        try {
            if (!file_exists($inputFile)) {
                throw new Exception("Input file not found: {$inputFile}");
            }
            
            $sourceCode = file_get_contents($inputFile);
            $obfuscatedCode = $this->obfuscateString($sourceCode);
            
            // Create wrapper code that deobfuscates and executes
            $wrapperCode = $this->createWrapperCode($obfuscatedCode);
            
            if (file_put_contents($outputFile, $wrapperCode) === false) {
                throw new Exception("Failed to write output file: {$outputFile}");
            }
            
            return true;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Create wrapper code that deobfuscates and executes the original code
     */
    private function createWrapperCode($obfuscatedCode)
    {
        $wrapper = '<?php' . "\n";
        $wrapper .= '// Obfuscated PHP Code - Generated by SimpleObfuscator' . "\n";
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
     * Generate a simple obfuscated version of PHP code
     */
    public function generateObfuscatedCode($sourceCode)
    {
        // Remove comments and extra whitespace
        $cleaned = preg_replace('/\/\*.*?\*\//s', '', $sourceCode);
        $cleaned = preg_replace('/\/\/.*$/m', '', $cleaned);
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        // Basic variable name obfuscation
        $obfuscated = $this->obfuscateString($cleaned);
        
        return $obfuscated;
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    echo "=== PHP Code Obfuscator & Deobfuscator Demo ===\n";
    echo "Note: This standalone obfuscator works without requiring Laravel framework\n";
    echo "This demo shows basic obfuscation and deobfuscation techniques\n\n";
    
    // Check for command line arguments
    $args = $argv;
    if (count($args) > 1) {
        $command = $args[1];
        
        if ($command === 'obfuscate:deobfuscate' && isset($args[2])) {
            $filePath = $args[2];
            $outputFile = isset($args[3]) ? $args[3] : null;
            
            echo "=== Testing Deobfuscation Command ===\n";
            echo "Input file: {$filePath}\n";
            
            $obfuscator = new SimpleObfuscator();
            
            // First analyze the file
            $analysis = $obfuscator->analyzeObfuscationLevel($filePath);
            echo "Obfuscation Analysis:\n";
            echo "  Is Obfuscated: " . ($analysis['is_obfuscated'] ? 'Yes' : 'No') . "\n";
            echo "  Confidence: {$analysis['confidence']}%\n";
            echo "  Patterns Found: " . implode(', ', $analysis['patterns_found']) . "\n";
            
            // Then deobfuscate
            $deobfuscatedFile = $obfuscator->deobfuscateFile($filePath, $outputFile);
            if ($deobfuscatedFile) {
                echo "✅ Deobfuscated successfully!\n";
                echo "Output file: {$deobfuscatedFile}\n";
                
                // Show content comparison
                $original = file_get_contents($filePath);
                $deobfuscated = file_get_contents($deobfuscatedFile);
                echo "\nContent Comparison:\n";
                echo "Original size: " . strlen($original) . " bytes\n";
                echo "Deobfuscated size: " . strlen($deobfuscated) . " bytes\n";
            } else {
                echo "❌ Deobfuscation failed!\n";
            }
            exit;
        }
    }
    
    // Default demo mode
    $obfuscator = new SimpleObfuscator();
    
    // Example: Obfuscate a simple PHP code string
    $sampleCode = '<?php echo "Hello, World!"; ?>';
    echo "Original code: " . $sampleCode . "\n";
    
    $obfuscated = $obfuscator->obfuscateString($sampleCode);
    echo "Obfuscated code: " . $obfuscated . "\n";
    
    $deobfuscated = $obfuscator->deobfuscateString($obfuscated);
    echo "Deobfuscated code: " . $deobfuscated . "\n";
    
    // Example: Obfuscate the example.php file
    if (file_exists('example.php')) {
        echo "\n=== File Obfuscation Test ===\n";
        echo "Obfuscating example.php...\n";
        if ($obfuscator->obfuscateFile('example.php', 'example_obfuscated.php')) {
            echo "✅ Successfully created example_obfuscated.php\n";
            
            // Test deobfuscation
            echo "\n=== File Deobfuscation Test ===\n";
            $deobfuscatedFile = $obfuscator->deobfuscateFile('example_obfuscated.php');
            if ($deobfuscatedFile) {
                echo "✅ File deobfuscated successfully!\n";
                echo "Deobfuscated file: {$deobfuscatedFile}\n";
            }
        }
    }
    
    echo "\n=== Usage Examples ===\n";
    echo "Test deobfuscation: php index.php obfuscate:deobfuscate file.php\n";
    echo "Test with output: php index.php obfuscate:deobfuscate file.php output.php\n";
    echo "\n=== Demo Complete ===\n";
} else {
    // Web interface
    echo "<h1>PHP Code Obfuscator Demo</h1>";
    echo "<p><strong>Note:</strong> This standalone obfuscator works without requiring Laravel framework</p>";
    echo "<p>This demo shows basic obfuscation techniques that work standalone.</p>";
    
    if (isset($_POST['code'])) {
        $obfuscator = new SimpleObfuscator();
        $inputCode = $_POST['code'];
        $obfuscatedCode = $obfuscator->obfuscateString($inputCode);
        
        echo "<h3>Results:</h3>";
        echo "<p><strong>Original:</strong> <code>" . htmlspecialchars($inputCode) . "</code></p>";
        echo "<p><strong>Obfuscated:</strong> <code>" . htmlspecialchars($obfuscatedCode) . "</code></p>";
    }
    
    echo "<form method='post'>";
    echo "<h3>Try it yourself:</h3>";
    echo "<textarea name='code' rows='5' cols='50' placeholder='Enter PHP code here...'><?php echo 'Hello World'; ?></textarea><br><br>";
    echo "<input type='submit' value='Obfuscate Code'>";
    echo "</form>";
}
