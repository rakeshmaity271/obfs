<?php

/**
 * Test script to verify obfuscated code execution
 * This file tests the example_obfuscated.php file
 */

echo "=== Testing Obfuscated Code ===\n\n";

// Test 1: Check if obfuscated file exists
if (file_exists('example_obfuscated.php')) {
    echo "✓ Obfuscated file found\n";
    
    // Test 2: Execute the obfuscated file
    echo "\n--- Executing obfuscated code ---\n";
    echo "Expected output:\n";
    echo "Hello, Test!\n";
    echo "Value: 42\n";
    echo "Calculated: 84\n\n";
    
    echo "Actual output:\n";
    include 'example_obfuscated.php';
    
    echo "\n--- Test completed ---\n";
} else {
    echo "✗ Obfuscated file not found\n";
}

// Test 3: Verify the obfuscation process
echo "\n=== Verification ===\n";
$original = file_get_contents('example.php');
$obfuscated = file_get_contents('example_obfuscated.php');

echo "Original file size: " . strlen($original) . " bytes\n";
echo "Obfuscated file size: " . strlen($obfuscated) . " bytes\n";
echo "Compression ratio: " . round((strlen($obfuscated) / strlen($original)) * 100, 2) . "%\n";

echo "\n=== All tests completed ===\n";
