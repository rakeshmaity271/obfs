<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Obfuscator Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the Laravel Obfuscator package.
    | You can customize these settings based on your needs.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | License Settings
    |--------------------------------------------------------------------------
    |
    | Configure license key and validation settings
    |
    */
    'license' => [
        'license_key' => env('OBFUSCATOR_LICENSE_KEY', ''),
        'demo_mode' => env('OBFUSCATOR_DEMO_MODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | Configure backup behavior for obfuscated files.
    |
    */
    'backup' => [
        'enabled' => env('OBFUSCATOR_BACKUP_ENABLED', true),
        'directory' => env('OBFUSCATOR_BACKUP_DIR', 'app/obfuscator_backups'),
        'keep_backups' => env('OBFUSCATOR_KEEP_BACKUPS', 10), // Number of backups to keep
    ],

    /*
    |--------------------------------------------------------------------------
    | Obfuscation Settings
    |--------------------------------------------------------------------------
    |
    | Configure obfuscation behavior and options.
    |
    */
    'obfuscation' => [
        'method' => env('OBFUSCATOR_METHOD', 'base64_reverse'), // base64_reverse, advanced
        'remove_comments' => env('OBFUSCATOR_REMOVE_COMMENTS', true),
        'remove_whitespace' => env('OBFUSCATOR_REMOVE_WHITESPACE', true),
        'preserve_php_tags' => env('OBFUSCATOR_PRESERVE_PHP_TAGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Patterns
    |--------------------------------------------------------------------------
    |
    | Define which files should be included or excluded from obfuscation.
    |
    */
    'patterns' => [
        'include' => [
            '*.php',
        ],
        'exclude' => [
            'vendor/**/*',
            'node_modules/**/*',
            'storage/**/*',
            'bootstrap/**/*',
            'config/**/*',
            'database/**/*',
            'resources/**/*',
            'routes/**/*',
            'tests/**/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Output Settings
    |--------------------------------------------------------------------------
    |
    | Configure output file naming and location.
    |
    */
    'output' => [
        'suffix' => env('OBFUSCATOR_OUTPUT_SUFFIX', '_obfuscated'),
        'preserve_structure' => env('OBFUSCATOR_PRESERVE_STRUCTURE', true),
        'overwrite_original' => env('OBFUSCATOR_OVERWRITE_ORIGINAL', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for obfuscation operations.
    |
    */
    'logging' => [
        'enabled' => env('OBFUSCATOR_LOGGING_ENABLED', true),
        'level' => env('OBFUSCATOR_LOG_LEVEL', 'info'),
        'channel' => env('OBFUSCATOR_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Obfuscation Options
    |--------------------------------------------------------------------------
    |
    | Configure advanced obfuscation techniques for different levels.
    |
    */
    'advanced_obfuscation' => [
        'randomize_variables' => env('OBFUSCATOR_RANDOMIZE_VARS', true),
        'encrypt_strings' => env('OBFUSCATOR_ENCRYPT_STRINGS', true),
        'control_flow_obfuscation' => env('OBFUSCATOR_CONTROL_FLOW', false),
        'dead_code_injection' => env('OBFUSCATOR_DEAD_CODE', false),
        'anti_debugging' => env('OBFUSCATOR_ANTI_DEBUG', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Interface Settings
    |--------------------------------------------------------------------------
    |
    | Configure the web interface behavior and limits.
    |
    */
    'web_interface' => [
        'max_file_size' => env('OBFUSCATOR_MAX_FILE_SIZE', 10485760), // 10MB
        'max_batch_files' => env('OBFUSCATOR_MAX_BATCH_FILES', 10),
        'allowed_file_types' => ['php'],
        'session_timeout' => env('OBFUSCATOR_SESSION_TIMEOUT', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Configure API behavior and rate limiting.
    |
    */
    'api' => [
        'rate_limit' => [
            'single_file' => env('OBFUSCATOR_API_RATE_LIMIT', 60), // requests per minute
            'batch_processing' => env('OBFUSCATOR_API_BATCH_LIMIT', 5), // batches per minute
        ],
        'max_file_size' => env('OBFUSCATOR_API_MAX_FILE_SIZE', 10485760), // 10MB
        'max_batch_size' => env('OBFUSCATOR_API_MAX_BATCH_SIZE', 10),
        'require_authentication' => env('OBFUSCATOR_API_AUTH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Directory
    |--------------------------------------------------------------------------
    |
    | Configure backup directory for obfuscated files.
    |
    */
    'backup_directory' => env('OBFUSCATOR_BACKUP_DIR', 'storage/app/obfuscator_backups'),

    /*
    |--------------------------------------------------------------------------
    | Output Directory
    |--------------------------------------------------------------------------
    |
    | Configure output directory for obfuscated files.
    |
    */
    'output_directory' => env('OBFUSCATOR_OUTPUT_DIR', 'storage/app/obfuscated'),
];
