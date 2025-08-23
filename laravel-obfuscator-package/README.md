# Laravel Obfuscator Package

A comprehensive Laravel package for PHP code obfuscation with backup and restore functionality. This package provides unique command structure with enhanced features and better integration for Laravel projects.

## Features

- 🚀 **Multiple Obfuscation Commands**: File, directory, and project-wide obfuscation
- 💾 **Automatic Backup System**: Create backups before obfuscation
- 🔄 **Restore Functionality**: Restore files from backups
- ⚙️ **Configurable**: Customize behavior through config files
- 📊 **Progress Tracking**: Detailed output with success/error reporting
- 🛡️ **Safe Operations**: Non-destructive with backup protection

## Installation

### Via Composer

```bash
composer require laravel-obfuscator/laravel-obfuscator
```

### Manual Installation

1. Clone this repository to your Laravel project
2. Add the service provider to `config/app.php`:

```php
'providers' => [
    // ... other providers
    LaravelObfuscator\LaravelObfuscator\LaravelObfuscatorServiceProvider::class,
],
```

3. Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-obfuscator-config
```

## Usage

### Artisan Commands

The package provides several Artisan commands to obfuscate PHP files within your Laravel project.

#### 🔒 **Secure Deployment Commands (RECOMMENDED)**

For **true security** when delivering to clients, use these commands:

```bash
# Secure deploy specific file (replaces original, moves original to secure backup)
php artisan obfuscate:file {file} --secure-deploy

# Secure deploy entire project (replaces all originals, moves originals to secure backup)
php artisan obfuscate:all --secure-deploy

# Secure deploy directory (replaces originals, moves originals to secure backup)
php artisan obfuscate:directory {directory} --secure-deploy

# Create secure deployment package with ZIP file
php artisan obfuscate:secure-deploy {source} [--output=path] [--exclude=path] [--level=enterprise] [--create-package]
```

**Why Secure Deployment?**
- ✅ **Client cannot access original source code**
- ✅ **Client cannot develop/modify your application**
- ✅ **Client cannot reverse-engineer your logic**
- ✅ **Only obfuscated code remains accessible**

#### **Basic Obfuscation Commands (Development Use)**

For development and testing (creates `_obfuscated` versions alongside originals):

```bash
# Obfuscate all PHP files (creates _obfuscated versions)
php artisan obfuscate:all

# With backup:
php artisan obfuscate:all --backup
```

#### Obfuscate Specific Directory

To obfuscate PHP files in a specific directory:

```bash
# Basic obfuscation (creates _obfuscated versions)
php artisan obfuscate:directory {directory}

# Secure deployment (replaces originals, moves originals to secure backup)
php artisan obfuscate:directory {directory} --secure-deploy

# Examples:
php artisan obfuscate:directory app/Http/Controllers
php artisan obfuscate:directory app/Models --backup
php artisan obfuscate:directory app/Http/Controllers --secure-deploy
```

#### Obfuscate Specific File

To obfuscate a specific PHP file:

```bash
# Basic obfuscation (creates _obfuscated version)
php artisan obfuscate:file {somefile or dir/file}

# Secure deployment (replaces original, moves original to secure backup)
php artisan obfuscate:file {somefile or dir/file} --secure-deploy

# Examples:
php artisan obfuscate:file app/Http/Controllers/UserController.php
php artisan obfuscate:file UserController.php --backup
php artisan obfuscate:file app/Http/Controllers/UserController.php --secure-deploy
```

#### Backup and Restore

**Backup**: You can create backups of obfuscated files with the `--backup` option:

```bash
php artisan obfuscate:all --backup
php artisan obfuscate:file app/Http/Controllers/UserController.php --backup
```

**Restore**: To restore a backed-up file:

```bash
php artisan obfuscate:restore {backup_file_name}
```

Example:
```bash
php artisan obfuscate:restore backup_1703123456_UserController.php
```

**Deobfuscate Examples:**
```bash
# Deobfuscate a single file
php artisan obfuscate:deobfuscate app/Http/Controllers/UserController.php

# Analyze obfuscation level only
php artisan obfuscate:deobfuscate app/Http/Controllers/UserController.php --analyze

# Deobfuscate with custom output path
php artisan obfuscate:deobfuscate app/Http/Controllers/UserController.php --output=deobfuscated.php

# Batch deobfuscate all PHP files in a directory
php artisan obfuscate:deobfuscate app/Http/Controllers --batch

# Deobfuscate all PHP files in project
php artisan deobfuscate:all

# Deobfuscate all PHP files in specific directory
php artisan deobfuscate:directory app/Http/Controllers

# Analyze all files without deobfuscating
php artisan deobfuscate:all --analyze

# Deobfuscate to custom output directory
php artisan deobfuscate:directory app/Models --output-dir=deobfuscated_models
```

#### Deobfuscate

To deobfuscate a PHP file or analyze its obfuscation level:

```bash
php artisan obfuscate:deobfuscate {file}
```

**Options:**
- `--output=path` - Specify output file path
- `--analyze` - Analyze obfuscation level without deobfuscating
- `--batch` - Process all PHP files in directory

**Examples:**
```bash
# Deobfuscate a single file
php artisan obfuscate:deobfuscate app/Http/Controllers/UserController.php

# Analyze obfuscation level only
php artisan obfuscate:deobfuscate app/Http/Controllers/UserController.php --analyze

# Deobfuscate with custom output path
php artisan obfuscate:deobfuscate app/Http/Controllers/UserController.php --output=deobfuscated.php

# Batch deobfuscate all PHP files in a directory
php artisan obfuscate:deobfuscate app/Http/Controllers --batch
```

#### Deobfuscate All Files

To deobfuscate all PHP files in your Laravel project:

```bash
php artisan deobfuscate:all
```

**Options:**
- `--output-dir=path` - Specify output directory for all deobfuscated files
- `--analyze` - Analyze obfuscation level without deobfuscating

**Examples:**
```bash
# Deobfuscate all PHP files
php artisan deobfuscate:all

# Analyze all files without deobfuscating
php artisan deobfuscate:all --analyze

# Deobfuscate all files to custom directory
php artisan deobfuscate:all --output-dir=deobfuscated_files
```

#### Deobfuscate Directory

To deobfuscate all PHP files in a specific directory:

```bash
php artisan deobfuscate:directory {directory}
```

**Options:**
- `--output-dir=path` - Specify output directory for deobfuscated files
- `--analyze` - Analyze obfuscation level without deobfuscating

**Examples:**
```bash
# Deobfuscate all PHP files in a directory
php artisan deobfuscate:directory app/Http/Controllers

# Analyze all files in directory
php artisan deobfuscate:directory app/Models --analyze

# Deobfuscate to custom output directory
php artisan deobfuscate:directory app/Http/Controllers --output-dir=deobfuscated_controllers
```

### Programmatic Usage

You can also use the obfuscator service directly in your code:

```php
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;

class SomeController extends Controller
{
    public function obfuscateCode(ObfuscatorService $obfuscator)
    {
        // Obfuscate a string
        $obfuscated = $obfuscator->obfuscateString('<?php echo "Hello World"; ?>');
        
        // Obfuscate a file
        $obfuscator->obfuscateFile('input.php', 'output.php', true);
        
        // Obfuscate a directory
        $results = $obfuscator->obfuscateDirectory('/path/to/directory', true);
        
        return response()->json($results);
    }
}
```

### Facade Usage

You can also use the facade alias:

```php
use Obfuscator;

// Obfuscate a string
$obfuscated = Obfuscator::obfuscateString('<?php echo "Hello"; ?>');

// Obfuscate a file
Obfuscator::obfuscateFile('input.php', 'output.php');
```

## Configuration

The package configuration file `config/laravel-obfuscator.php` allows you to customize:

- **Backup Settings**: Enable/disable backups, backup directory, retention
- **Obfuscation Settings**: Method, comment removal, whitespace handling
- **File Patterns**: Include/exclude specific files and directories
- **Output Settings**: File naming, structure preservation
- **Logging**: Enable logging with configurable levels

### Environment Variables

You can also configure the package using environment variables:

```env
OBFUSCATOR_BACKUP_ENABLED=true
OBFUSCATOR_BACKUP_DIR=app/obfuscator_backups
OBFUSCATOR_METHOD=base64_reverse
OBFUSCATOR_REMOVE_COMMENTS=true
OBFUSCATOR_OUTPUT_SUFFIX=_obfuscated
```

## File Structure

```
src/
├── Console/
│   ├── Commands/
│   │   ├── ObfuscateCommand.php           # obfuscate:file
│   │   ├── ObfuscateAllCommand.php        # obfuscate:all
│   │   ├── ObfuscateDirectoryCommand.php  # obfuscate:directory
│   │   ├── RestoreCommand.php             # obfuscate:restore
│   │   ├── DeobfuscateCommand.php         # obfuscate:deobfuscate
│   │   ├── DeobfuscateAllCommand.php      # deobfuscate:all
│   │   └── DeobfuscateDirectoryCommand.php # deobfuscate:directory
│   └── ...
├── Services/
│   ├── ObfuscatorService.php              # Main service class
│   └── DeobfuscatorService.php            # Deobfuscation service
├── LaravelObfuscatorServiceProvider.php   # Service provider
└── ...
config/
└── laravel-obfuscator.php                 # Configuration file
```

## Backup System

The package automatically creates backups in `storage/app/obfuscator_backups/` when using the `--backup` option. Backup files are named with the pattern:

```
backup_{timestamp}_{original_filename}
```

Example: `backup_1703123456_UserController.php`

## Obfuscation Method

The package uses a base64 encoding + string reversal technique that:

1. Encodes the PHP code using base64
2. Reverses the encoded string
3. Generates a wrapper that deobfuscates and executes the code at runtime

This provides a good balance between obfuscation effectiveness and performance.

## Security Considerations

- **Backup Protection**: Always use the `--backup` option in production
- **Testing**: Test obfuscated code thoroughly before deployment
- **Source Control**: Keep original source code in version control
- **Performance**: Obfuscated code has a small runtime overhead

## Troubleshooting

### Common Issues

1. **Permission Errors**: Ensure write permissions for backup and output directories
2. **Memory Issues**: For large projects, increase PHP memory limit
3. **Backup Not Found**: Check the backup directory path in configuration

### Debug Mode

Enable detailed logging by setting the log level to debug:

```env
OBFUSCATOR_LOG_LEVEL=debug
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For support and questions:

- Create an issue on GitHub
- Check the documentation
- Review the configuration options

## Changelog

### Version 1.0.0
- Initial release
- Basic obfuscation functionality
- Artisan commands for file, directory, and project obfuscation
- Backup and restore system
- Configuration system

## 🔒 **Secure Deployment Mode**

### **The Problem with Basic Obfuscation**
Basic obfuscation creates `_obfuscated` files alongside originals, which means:
- ❌ **Client can still access original source code**
- ❌ **Client can still develop/modify the application**
- ❌ **Obfuscation provides no real security**

### **The Solution: Secure Deployment Mode**
Secure deployment mode provides **true security** by:
- ✅ **Replacing original files** with obfuscated versions
- ✅ **Moving originals to secure backup** (client cannot access)
- ✅ **Creating deployment packages** (ZIP files with only obfuscated code)
- ✅ **Ensuring clients cannot reverse-engineer** your application

### **Secure Deployment Commands**

#### **Single File Secure Deployment**
```bash
# Replace original file with obfuscated version
php artisan obfuscate:file app/Http/Controllers/Controller.php --secure-deploy

# Create deployment package
php artisan obfuscate:file app/Http/Controllers/Controller.php --secure-deploy --create-package
```

#### **Directory Secure Deployment**
```bash
# Secure deploy entire directory
php artisan obfuscate:directory app/Http/Controllers --secure-deploy

# Exclude certain files from obfuscation
php artisan obfuscate:directory app --secure-deploy --exclude=vendor --exclude=storage
```

#### **Project-Wide Secure Deployment**
```bash
# Secure deploy entire project
php artisan obfuscate:all --secure-deploy

# Create deployment package
php artisan obfuscate:all --secure-deploy --create-package
```

#### **Dedicated Secure Deployment Command**
```bash
# Create secure deployment package with ZIP
php artisan obfuscate:secure-deploy app --create-package --output=deployments

# Enterprise-level obfuscation for maximum security
php artisan obfuscate:secure-deploy app --level=enterprise --create-package
```

### **What Happens in Secure Deployment Mode**

1. **🔒 Original files are backed up** to `storage/app/secure_deployment_backups/`
2. **🔒 Original files are replaced** with obfuscated versions
3. **🔒 Secure backup location** is NOT accessible to clients
4. **🔒 Only obfuscated code** remains in the project
5. **📦 Optional deployment package** (ZIP) can be created

### **Security Benefits**

- **🚫 No Source Code Access**: Clients cannot read your original code
- **🚫 No Development Capability**: Clients cannot modify your logic
- **🚫 No Reverse Engineering**: Obfuscated code is extremely difficult to understand
- **✅ Full Functionality**: Application works exactly the same
- **✅ Professional Delivery**: Clean, production-ready codebase

### **When to Use Secure Deployment**

- **🎯 Client Deliverables**: When handing over code to clients
- **🚀 Production Deployment**: When deploying to production servers
- **📦 Software Distribution**: When selling/distributing your software
- **🔐 Code Protection**: When protecting intellectual property
- **💼 Business Applications**: When delivering business solutions to clients

## 🚀 **Quick Start**
