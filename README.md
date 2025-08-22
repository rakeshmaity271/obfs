# PHP Code Obfuscator & Laravel Package

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-9%2B-red.svg)](https://laravel.com)

A comprehensive PHP code obfuscation solution featuring both a **standalone obfuscator** and a **professional Laravel package**. Our `LaravelObfuscator` package provides enhanced features and better integration for Laravel projects.

## Requirements

- PHP 7.4 or higher
- Composer
- Valid LaravelObfuscator license key

## 🚀 Quick Start

### For Laravel Projects (Recommended)

Install our Laravel package directly in your Laravel project:

```bash
composer require laravel-obfuscator/laravel-obfuscator
```

Or use our package from this repository:

1. Copy the `laravel-obfuscator-package/` directory to your Laravel project
2. Add the service provider to `config/app.php`
3. Publish configuration: `php artisan vendor:publish --tag=laravel-obfuscator-config`
4. Set your license key in `.env`: `OBFUSCATOR_LICENSE_KEY=DEMO-1234-5678-9ABC`

### For Standalone Use

1. **Clone this repository:**
   ```bash
   git clone https://github.com/rakeshmaity271/obfs.git
   cd obfs
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Run the obfuscator:**
   ```bash
   php index.php
   ```

## Usage

### Command Line Interface

Run the obfuscator from the command line:

```bash
php index.php
```

### Web Interface

Access the project through a web server to see the web interface with a form for testing obfuscation.

### Programmatic Usage

```php
<?php
require_once 'index.php';

$obfuscator = new SimpleObfuscator();

// Obfuscate a string of PHP code
$obfuscated = $obfuscator->obfuscateString('<?php echo "Hello"; ?>');

// Obfuscate a PHP file
$obfuscator->obfuscateFile('input.php', 'output.php');
```

## Features

- **License Protection**: Enterprise-grade license key system with feature restrictions
- **String Obfuscation**: Obfuscate PHP code strings using base64 encoding and reversal
- **File Obfuscation**: Obfuscate entire PHP files with automatic wrapper generation
- **Deobfuscation**: Reverse the obfuscation process to retrieve original code
- **Advanced Deobfuscation**: Multiple deobfuscation techniques and analysis tools
- **Error Handling**: Comprehensive error handling and validation
- **CLI & Web Support**: Works both from command line and web interface
- **Standalone Operation**: Works without requiring Laravel framework

## Our Laravel Package

We've developed a complete Laravel package (`LaravelObfuscator`) that provides:

### **Artisan Commands:**
- `php artisan obfuscate:all` - Obfuscate all PHP files
- `php artisan obfuscate:directory {directory}` - Obfuscate specific directory
- `php artisan obfuscate:file {file}` - Obfuscate specific file
- `php artisan obfuscate:restore {backup}` - Restore from backup
- `php artisan obfuscate:deobfuscate {file}` - Deobfuscate a PHP file
- `php artisan deobfuscate:all` - Deobfuscate all PHP files
- `php artisan deobfuscate:directory {directory}` - Deobfuscate specific directory
- `php artisan obfuscate:license {action}` - Manage license (status, validate, info)

### **Package Features:**
- **License Protection**: Enterprise-grade license key system
- Professional Laravel service provider
- Configuration system
- Backup and restore functionality
- Progress tracking and error reporting
- Feature restrictions based on license plan
- Ready for Packagist publication

## Obfuscation Technique

The obfuscator uses a simple but effective technique:
1. Base64 encode the source code
2. Reverse the encoded string
3. Generate a wrapper that deobfuscates and executes the code

## Deobfuscation Features

Our package includes advanced deobfuscation capabilities:

### **Command Line Obfuscation**
```bash
# Basic obfuscation (creates _obfuscated files)
php artisan obfuscate:file file.php
php artisan obfuscate:directory app/Http/Controllers
php artisan obfuscate:all

# Replace original files (DANGEROUS!)
php artisan obfuscate:file file.php --replace
php artisan obfuscate:directory app/Models --replace
php artisan obfuscate:all --replace

# Custom output paths
php artisan obfuscate:file file.php --output=custom_name.php
php artisan obfuscate:directory app/ --output-dir=obfuscated/
```

### **Command Line Deobfuscation**
```bash
# Basic deobfuscation
php artisan obfuscate:deobfuscate file.php

# Analyze obfuscation level without deobfuscating
php artisan obfuscate:deobfuscate file.php --analyze

# Custom output path
php artisan obfuscate:deobfuscate file.php --output=deobfuscated.php

# Batch process directory
php artisan obfuscate:deobfuscate directory/ --batch

# Deobfuscate all PHP files in project
php artisan deobfuscate:all

# Deobfuscate all PHP files in specific directory
php artisan deobfuscate:directory app/Http/Controllers

# Analyze all files without deobfuscating
php artisan deobfuscate:all --analyze

# Deobfuscate to custom output directory
php artisan deobfuscate:directory app/Models --output-dir=deobfuscated_models
```

### **Deobfuscation Techniques**
- **Base64 Reverse**: Decode reversed base64 strings
- **Variable De-randomization**: Restore original variable names
- **String Decryption**: Decrypt encrypted string literals
- **Dead Code Removal**: Clean up injected dead code
- **Anti-Debugging Removal**: Remove anti-debugging measures
- **Obfuscation Analysis**: Detect obfuscation techniques and levels

## Project Structure

```
obf/
├── composer.json                    # Composer configuration
├── index.php                       # Main application file with SimpleObfuscator class
├── example.php                     # Sample PHP file for testing obfuscation
├── example_obfuscated.php         # Generated obfuscated version of example.php
├── test_obfuscated.php            # Test script to verify obfuscated code
├── README.md                       # This file
├── .gitignore                      # Git ignore rules
├── LICENSE                         # MIT license
├── laravel-obfuscator-package/    # Our Laravel package
│   ├── src/                       # Package source code
│   │   ├── Console/Commands/      # Artisan commands (obfuscate:*, deobfuscate)
│   │   ├── Services/              # Obfuscator & Deobfuscator services
│   │   └── Models/                # User, Project, Audit models
│   ├── config/                    # Configuration files
│   ├── composer.json              # Package composer.json
│   ├── README.md                  # Package documentation
│   └── LICENSE                    # MIT license
└── vendor/                        # Composer dependencies
```

## Dependencies

- **Our Own Package**: `LaravelObfuscator\LaravelObfuscator` - Complete Laravel obfuscator
- **PHP 7.4+**: Required PHP version

## Testing

Run the test script to verify obfuscation works correctly:

```bash
php test_obfuscated.php
```

This will:
- Execute the obfuscated code
- Compare output with expected results
- Show file size comparison

## License System

### **Available License Plans:**

- **Demo License** (`DEMO-1234-5678-9ABC`): 30 days, basic features, 10 files max
- **Trial License** (`TRIAL-ABCD-EFGH-IJKL`): 7 days, full features, 50 files max  
- **Professional License** (`PRO-1234-5678-9ABC`): 1 year, unlimited features
- **Unlimited License** (`--days=0`): Never expires, unlimited features

**Note**: All licenses work offline - no remote server needed!

### **License Protection:**

**🔒 ALL Commands Now Require Valid License:**
- ✅ **Obfuscation Commands**: `obfuscate:file`, `obfuscate:all`, `obfuscate:directory`
- ✅ **Deobfuscation Commands**: `deobfuscate:all`, `deobfuscate:directory`, `obfuscate:deobfuscate`
- ✅ **Utility Commands**: `obfuscate:restore`, `obfuscate:scheduled`
- ✅ **Web Interface & API**: All obfuscation/deobfuscation features
- ✅ **Project Management**: File processing and automation

**🔓 License Management Commands (Always Available):**
- `obfuscate:license status` - Check license status
- `obfuscate:license validate` - Validate license key
- `obfuscate:license info` - Show detailed license info
- `obfuscate:generate-license` - Generate new license keys

### **License Commands:**
```bash
# Check license status
php artisan obfuscate:license status

# Validate a license key
php artisan obfuscate:license validate --key=YOUR_KEY

# Show detailed license info
php artisan obfuscate:license info

# Generate custom license keys
php artisan obfuscate:generate-license demo --days=30 --files=10 --size=1
php artisan obfuscate:generate-license pro --days=365 --files=0 --size=0 --customer="Your Company"
php artisan obfuscate:generate-license pro --days=0 --files=0 --size=0 --customer="Unlimited License"
```

## Notes

- We've developed our own Laravel obfuscator package with unique features
- Our package provides the same command structure with enhanced features
- The standalone obfuscator works without requiring Laravel framework
- The Laravel package is ready for publication to Packagist
- **License protection ensures your obfuscation technology is secure**

## 🤝 Contributing

We welcome contributions! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Changelog

### Version 1.0.0 (2024)
- Initial release
- Complete Laravel package with 7 Artisan commands
- Standalone obfuscator functionality
- Advanced deobfuscation capabilities
- Backup and restore system
- Professional configuration system
- Unique command structure with enhanced features

## 🐛 Issue Reporting

Found a bug? Please open an issue with:
- PHP version
- Laravel version (if applicable)
- Steps to reproduce
- Expected vs actual behavior

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🙏 Acknowledgments

- Built with ❤️ for the Laravel community
- Built with ❤️ for the Laravel community
- Thanks to all contributors

---

**⭐ If this project helped you, please give it a star!**

