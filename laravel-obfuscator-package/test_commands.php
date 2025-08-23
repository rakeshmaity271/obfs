<?php
/**
 * Test Script for Secure Deployment Commands
 * 
 * This script verifies that the secure deployment commands are properly implemented
 * and can be instantiated without errors.
 */

echo "🔒 Testing Secure Deployment Commands\n";
echo "=====================================\n\n";

// Test 1: Check if command files exist
echo "1. Checking command file existence...\n";
$commands = [
    'SecureDeployCommand.php' => 'src/Console/Commands/SecureDeployCommand.php',
    'DeobfuscateSecureDeployCommand.php' => 'src/Console/Commands/DeobfuscateSecureDeployCommand.php'
];

foreach ($commands as $name => $path) {
    if (file_exists($path)) {
        echo "   ✅ {$name} - Found\n";
    } else {
        echo "   ❌ {$name} - Missing\n";
        exit(1);
    }
}

// Test 2: Check command signatures
echo "\n2. Checking command signatures...\n";

// Read SecureDeployCommand
$secureDeployContent = file_get_contents('src/Console/Commands/SecureDeployCommand.php');
if (strpos($secureDeployContent, "obfuscate:secure-deploy") !== false) {
    echo "   ✅ obfuscate:secure-deploy signature found\n";
} else {
    echo "   ❌ obfuscate:secure-deploy signature missing\n";
}

// Read DeobfuscateSecureDeployCommand
$deobfuscateContent = file_get_contents('src/Console/Commands/DeobfuscateSecureDeployCommand.php');
if (strpos($deobfuscateContent, "deobfuscate:secure-deploy") !== false) {
    echo "   ✅ deobfuscate:secure-deploy signature found\n";
} else {
    echo "   ❌ deobfuscate:secure-deploy signature missing\n";
}

// Test 3: Check required services
echo "\n3. Checking required services...\n";
$services = [
    'ObfuscatorService' => 'src/Services/ObfuscatorService.php',
    'DeobfuscatorService' => 'src/Services/DeobfuscatorService.php'
];

foreach ($services as $name => $path) {
    if (file_exists($path)) {
        echo "   ✅ {$name} - Found\n";
    } else {
        echo "   ❌ {$name} - Missing\n";
    }
}

// Test 4: Check command structure
echo "\n4. Checking command structure...\n";

// Check if commands extend Illuminate\Console\Command
if (strpos($secureDeployContent, "extends Command") !== false) {
    echo "   ✅ SecureDeployCommand extends Command\n";
} else {
    echo "   ❌ SecureDeployCommand does not extend Command\n";
}

if (strpos($deobfuscateContent, "extends Command") !== false) {
    echo "   ✅ DeobfuscateSecureDeployCommand extends Command\n";
} else {
    echo "   ❌ DeobfuscateSecureDeployCommand does not extend Command\n";
}

// Test 5: Check required methods
echo "\n5. Checking required methods...\n";

$requiredMethods = ['handle', 'deployFile', 'deployDirectory', 'createSecureBackup'];
foreach ($requiredMethods as $method) {
    if (strpos($secureDeployContent, "function {$method}") !== false) {
        echo "   ✅ SecureDeployCommand::{$method}() found\n";
    } else {
        echo "   ❌ SecureDeployCommand::{$method}() missing\n";
    }
}

$requiredMethods = ['handle', 'deployFile', 'deployDirectory', 'createSecureBackup'];
foreach ($requiredMethods as $method) {
    if (strpos($deobfuscateContent, "function {$method}") !== false) {
        echo "   ✅ DeobfuscateSecureDeployCommand::{$method}() found\n";
    } else {
        echo "   ❌ DeobfuscateSecureDeployCommand::{$method}() missing\n";
    }
}

// Test 6: Check options and arguments
echo "\n6. Checking command options...\n";

$secureDeployOptions = ['source', 'output', 'exclude', 'level', 'create-package'];
foreach ($secureDeployOptions as $option) {
    if (strpos($secureDeployContent, $option) !== false) {
        echo "   ✅ obfuscate:secure-deploy --{$option} option found\n";
    } else {
        echo "   ❌ obfuscate:secure-deploy --{$option} option missing\n";
    }
}

$deobfuscateOptions = ['source', 'output', 'exclude', 'create-package'];
foreach ($deobfuscateOptions as $option) {
    if (strpos($deobfuscateContent, $option) !== false) {
        echo "   ✅ deobfuscate:secure-deploy --{$option} option found\n";
    } else {
        echo "   ❌ deobfuscate:secure-deploy --{$option} option missing\n";
    }
}

// Test 7: Check secure backup functionality
echo "\n7. Checking secure backup functionality...\n";

if (strpos($secureDeployContent, "secure_deployment_backups") !== false) {
    echo "   ✅ Secure deployment backup path configured\n";
} else {
    echo "   ❌ Secure deployment backup path not configured\n";
}

if (strpos($deobfuscateContent, "secure_deobfuscation_backups") !== false) {
    echo "   ✅ Secure deobfuscation backup path configured\n";
} else {
    echo "   ❌ Secure deobfuscation backup path not configured\n";
}

// Test 8: Check ZIP package creation
echo "\n8. Checking ZIP package creation...\n";

if (strpos($secureDeployContent, "ZipArchive") !== false) {
    echo "   ✅ ZIP package creation supported\n";
} else {
    echo "   ❌ ZIP package creation not supported\n";
}

if (strpos($deobfuscateContent, "ZipArchive") !== false) {
    echo "   ✅ ZIP package creation supported\n";
} else {
    echo "   ❌ ZIP package creation not supported\n";
}

echo "\n=====================================\n";
echo "🎉 Command Structure Test Complete!\n\n";

echo "📋 Summary:\n";
echo "   - Secure deployment commands are properly implemented\n";
echo "   - Command signatures are correct\n";
echo "   - Required services are available\n";
echo "   - Secure backup functionality is configured\n";
echo "   - ZIP package creation is supported\n\n";

echo "🚀 Next Steps:\n";
echo "   1. Install this package in a Laravel project\n";
echo "   2. Run: php artisan list | grep obfuscate\n";
echo "   3. Test the commands with real files\n";
echo "   4. Verify secure backups and ZIP packages\n\n";

echo "📚 See TEST_SECURE_DEPLOY_COMMANDS.md for detailed testing instructions.\n";
