# 🔧 Laravel Integration Testing Guide

## 🧪 **Test Your Package in Real Laravel Projects**

### **Option 1: Local Laravel Project Testing**

1. **Create a new Laravel project:**
   ```bash
   composer create-project laravel/laravel test-obfuscator
   cd test-obfuscator
   ```

2. **Install your package locally:**
   ```bash
   # Add local repository to composer.json
   composer config repositories.laravel-obfuscator path ../obf/laravel-obfuscator-package
   
   # Install the package
   composer require laravel-obfuscator/laravel-obfuscator:dev-main
   ```

3. **Publish configuration:**
   ```bash
   php artisan vendor:publish --tag=laravel-obfuscator-config
   ```

4. **Test the commands:**
   ```bash
   # Create a test PHP file
   echo '<?php echo "Hello World!"; ?>' > test.php
   
   # Test obfuscation
php artisan obfuscate:file test.php

# Check if obfuscated file was created
ls -la test_obfuscated.php

# Test deobfuscation
php artisan obfuscate:deobfuscate test_obfuscated.php

# Test deobfuscation analysis
php artisan obfuscate:deobfuscate test_obfuscated.php --analyze

# Test deobfuscate all files
php artisan deobfuscate:all --analyze

# Test deobfuscate directory
php artisan deobfuscate:directory app/Http/Controllers --analyze
   ```

### **Option 2: GitHub Integration Testing**

1. **Fork your repository** to a test account
2. **Create a test Laravel project** in the forked repo
3. **Test the package** in that environment
4. **Report issues** back to the main repository

### **Option 3: CI/CD Testing**

Create GitHub Actions to test your package:

```yaml
# .github/workflows/test.yml
name: Test Laravel Package

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Install Composer
      run: composer install
      
    - name: Test Package
      run: |
        cd laravel-obfuscator-package
        composer validate
        composer install --prefer-dist --no-dev
```

### **Testing Checklist**

- [ ] Package installs without errors
- [ ] Configuration publishes correctly
- [ ] All 7 Artisan commands work (including comprehensive deobfuscator)
- [ ] Backup system functions properly
- [ ] Restore system works correctly
- [ ] Deobfuscation works correctly
- [ ] Error handling works as expected
- [ ] Progress tracking displays correctly

---

**Ready to test? Start with Option 1 for quick local testing!** 🚀
