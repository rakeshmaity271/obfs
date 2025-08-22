# PHP Code Obfuscator & Laravel Package

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-9%2B-red.svg)](https://laravel.com)

A comprehensive PHP code obfuscation solution featuring both a **standalone obfuscator** and a **professional Laravel package**. Our `LaravelObfuscator` package provides enhanced features and better integration for Laravel projects.

## Requirements

- PHP 7.4 or higher
- Composer

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

- **String Obfuscation**: Obfuscate PHP code strings using base64 encoding and reversal
- **File Obfuscation**: Obfuscate entire PHP files with automatic wrapper generation
- **Deobfuscation**: Reverse the obfuscation process to retrieve original code
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

### **Package Features:**
- Professional Laravel service provider
- Configuration system
- Backup and restore functionality
- Progress tracking and error reporting
- Ready for Packagist publication

## Obfuscation Technique

The obfuscator uses a simple but effective technique:
1. Base64 encode the source code
2. Reverse the encoded string
3. Generate a wrapper that deobfuscates and executes the code

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

## Notes

- We've developed our own Laravel obfuscator package with unique features
- Our package provides the same command structure with enhanced features
- The standalone obfuscator works without requiring Laravel framework
- The Laravel package is ready for publication to Packagist

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
- Complete Laravel package with 4 Artisan commands
- Standalone obfuscator functionality
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

