# Secure Deployment Commands

LaravelObfuscator provides streamlined secure deployment functionality for both obfuscation and deobfuscation operations. These commands ensure that your original source code is securely backed up and replaced with the processed versions, making them ideal for client deployment scenarios.

## 🔒 **Obfuscation Secure Deploy Command**

### Create Secure Deployment Package
```bash
php artisan obfuscate:secure-deploy {source} [--output=path] [--exclude=path] [--level=enterprise] [--create-package]
```
**Purpose**: Create a complete secure deployment package with obfuscated code that clients cannot reverse-engineer.

**Options**:
- `--output=path`: Output directory for deployment package
- `--exclude=path`: Files/directories to exclude from obfuscation
- `--level=enterprise`: Obfuscation level (basic, advanced, enterprise)
- `--create-package`: Create a deployment package (ZIP file)

**Features**:
- Creates secure backup of source
- Obfuscates all PHP files
- Replaces originals with obfuscated versions
- Optionally creates ZIP deployment package
- Enterprise-grade security

**Example**:
```bash
php artisan obfuscate:secure-deploy app --output=deployments --exclude=vendor --level=enterprise --create-package
```

## 🔓 **Deobfuscation Secure Deploy Command**

### Create Secure Deobfuscation Deployment Package
```bash
php artisan deobfuscate:secure-deploy {source} [--output=path] [--exclude=path] [--create-package]
```
**Purpose**: Create a complete secure deployment package with deobfuscated code that clients can understand and maintain.

**Options**:
- `--output=path`: Output directory for deployment package
- `--exclude=path`: Files/directories to exclude from deobfuscation
- `--create-package`: Create a deployment package (ZIP file)

**Features**:
- Creates secure backup of obfuscated source
- Deobfuscates all PHP files
- Replaces obfuscated with deobfuscated versions
- Optionally creates ZIP deployment package
- Client-ready maintainable code

**Example**:
```bash
php artisan deobfuscate:secure-deploy app --output=deployments --exclude=vendor --create-package
```

## 🔐 **Security Features**

### Secure Backup Locations
All secure deployment commands create backups in secure locations that are not accessible to clients:

- **Obfuscation backups**: `storage/app/secure_deployment_backups/`
- **Deobfuscation backups**: `storage/app/secure_deobfuscation_backups/`

### Backup Naming Convention
Backups are automatically named with timestamps to prevent conflicts:
```
secure_deployment_backups/2024-01-15_14-30-25/
secure_deobfuscation_backups/2024-01-15_14-30-25/
```

### File Permissions
Secure backup directories are created with restricted permissions (0750) to ensure client access is blocked.

## ⚠️ **Safety Features**

### Confirmation Prompts
All secure deployment commands require explicit confirmation before proceeding:
- Clear warnings about irreversible actions
- Detailed explanation of what will happen
- Option to cancel at any time

### Automatic Backup Creation
- Secure deployment automatically forces backup creation
- Original files are never lost
- Easy restoration if needed

### Progress Monitoring
- Progress bars for large operations
- Detailed status reporting
- Error handling and recovery

## 🚀 **Use Cases**

### Obfuscation Secure Deploy
- **Client Deployment**: Deliver obfuscated code that clients cannot reverse-engineer
- **Production Releases**: Secure production code from unauthorized access
- **License Protection**: Protect intellectual property in distributed applications
- **Security Audits**: Ensure code security compliance

### Deobfuscation Secure Deploy
- **Code Maintenance**: Transition from obfuscated to maintainable code
- **Client Handover**: Provide readable code for client maintenance
- **Development Teams**: Enable development teams to work with readable code
- **Code Reviews**: Facilitate code review and quality assurance

## 📋 **Command Summary**

| Command | Purpose | Description |
|---------|---------|-------------|
| `obfuscate:secure-deploy` | Create obfuscation package | Built-in secure deploy with obfuscated code |
| `deobfuscate:secure-deploy` | Create deobfuscation package | Built-in secure deploy with readable code |

## 🔄 **Restoration**

If you need to restore from secure backups, you can manually copy files from the secure backup directories or use the existing restore commands for regular backups.

## 📚 **Additional Resources**

- [Main README](../README.md)
- [Advanced Features Roadmap](../ADVANCED_FEATURES_ROADMAP.md)
- [Laravel Testing Guide](../LARAVEL_TESTING_GUIDE.md)
- [Phase 2 Implementation Summary](../PHASE2_IMPLEMENTATION_SUMMARY.md)
- [Phase 3 Future Roadmap](../PHASE3_FUTURE_ROADMAP.md)
