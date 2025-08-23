# 🔒 Testing Secure Deployment Commands

This guide shows how to test the secure deployment commands in a Laravel environment.

## 🚀 **Setup for Testing**

### 1. **Install in a Laravel Project**
```bash
# Copy this package to your Laravel project
cp -r laravel-obfuscator-package /path/to/your/laravel/project/

# Add to composer.json
composer require laravel-obfuscator/laravel-obfuscator

# Publish configuration
php artisan vendor:publish --tag=laravel-obfuscator-config

# Set license key in .env
OBFUSCATOR_LICENSE_KEY=DEMO-1234-5678-9ABC
```

### 2. **Verify Commands are Available**
```bash
php artisan list | grep obfuscate
php artisan list | grep deobfuscate
```

You should see:
- `obfuscate:secure-deploy`
- `deobfuscate:secure-deploy`

## 🧪 **Test 1: Obfuscation Secure Deployment**

### **Test Single File**
```bash
# Create a test PHP file
echo '<?php echo "Hello World"; ?>' > test_file.php

# Test secure deployment
php artisan obfuscate:secure-deploy test_file.php --create-package

# Expected Results:
# ✅ Original file backed up to secure location
# ✅ File replaced with obfuscated version
# ✅ ZIP package created (if --create-package used)
# ✅ Original source code no longer accessible
```

### **Test Directory**
```bash
# Create test directory with PHP files
mkdir test_app
echo '<?php echo "App 1"; ?>' > test_app/app1.php
echo '<?php echo "App 2"; ?>' > test_app/app2.php

# Test secure deployment
php artisan obfuscate:secure-deploy test_app --create-package --exclude=app2.php

# Expected Results:
# ✅ All PHP files backed up to secure location
# ✅ Files replaced with obfuscated versions
# ✅ app2.php excluded (not processed)
# ✅ ZIP package created
```

## 🧪 **Test 2: Deobfuscation Secure Deployment**

### **Test Single File**
```bash
# Use the obfuscated file from previous test
php artisan deobfuscate:secure-deploy test_file.php --create-package

# Expected Results:
# ✅ Obfuscated file backed up to secure location
# ✅ File replaced with readable deobfuscated version
# ✅ ZIP package created with readable code
# ✅ Client can now understand and maintain code
```

### **Test Directory**
```bash
# Deobfuscate entire directory
php artisan deobfuscate:secure-deploy test_app --create-package

# Expected Results:
# ✅ All obfuscated files backed up to secure location
# ✅ Files replaced with readable deobfuscated versions
# ✅ ZIP package created with maintainable code
```

## 🔍 **Verification Steps**

### **1. Check Secure Backups**
```bash
# Obfuscation backups
ls -la storage/app/secure_deployment_backups/

# Deobfuscation backups
ls -la storage/app/secure_deobfuscation_backups/
```

### **2. Verify File Replacement**
```bash
# Check if original files are replaced
cat test_file.php
cat test_app/app1.php

# Should show obfuscated/deobfuscated code, not original
```

### **3. Check ZIP Packages**
```bash
# Look for deployment packages
ls -la *.zip
ls -la deployments/*.zip
```

## ⚠️ **Important Test Notes**

### **Security Verification**
- ✅ **Original source code should NOT be accessible** after obfuscation
- ✅ **Secure backup locations should be protected** (0750 permissions)
- ✅ **Only processed code should remain** in the project

### **Functionality Verification**
- ✅ **Obfuscated code should execute** without errors
- ✅ **Deobfuscated code should be readable** and maintainable
- ✅ **ZIP packages should contain** only the processed code

### **Error Handling**
- ✅ **Invalid source paths** should show clear error messages
- ✅ **Permission issues** should be handled gracefully
- ✅ **Missing dependencies** should show helpful guidance

## 🎯 **Test Scenarios**

### **Scenario 1: Client Delivery**
```bash
# 1. Obfuscate entire application
php artisan obfuscate:secure-deploy app --create-package --output=client_delivery

# 2. Verify client cannot access source
# 3. Verify application still works
# 4. Deliver ZIP package to client
```

### **Scenario 2: Code Maintenance**
```bash
# 1. Deobfuscate for maintenance
php artisan deobfuscate:secure-deploy app --create-package --output=maintenance

# 2. Make code changes
# 3. Re-obfuscate for delivery
php artisan obfuscate:secure-deploy app --create-package --output=updated_delivery
```

### **Scenario 3: Selective Processing**
```bash
# Exclude sensitive files
php artisan obfuscate:secure-deploy app \
  --exclude=config/database.php \
  --exclude=storage \
  --exclude=vendor \
  --create-package
```

## 🚨 **Troubleshooting**

### **Common Issues**
1. **Command not found**: Check if package is properly installed
2. **Permission denied**: Ensure write permissions for storage directories
3. **License error**: Verify license key is set correctly
4. **File not found**: Check source path exists and is accessible

### **Debug Mode**
```bash
# Enable verbose output
php artisan obfuscate:secure-deploy test_file.php -v

# Check logs
tail -f storage/logs/laravel.log
```

## ✅ **Success Criteria**

The secure deployment commands are working correctly if:

1. **🔒 Security**: Original source code is completely inaccessible
2. **📦 Functionality**: Processed code executes without errors
3. **💾 Backup**: Secure backups are created in protected locations
4. **📁 Replacement**: Files are properly replaced in place
5. **🎯 Packages**: ZIP deployment packages are created successfully
6. **🛡️ Permissions**: Secure backup directories have restricted access

## 🎉 **Test Complete!**

Once all tests pass, your secure deployment commands are ready for production use!
