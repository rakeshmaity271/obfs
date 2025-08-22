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
];
