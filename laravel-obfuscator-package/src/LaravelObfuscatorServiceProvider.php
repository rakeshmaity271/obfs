<?php

namespace LaravelObfuscator\LaravelObfuscator;

use Illuminate\Support\ServiceProvider;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\ObfuscateCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\ObfuscateAllCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\ObfuscateDirectoryCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\RestoreCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\DeobfuscateCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\ScheduledObfuscationCommand;

class LaravelObfuscatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ObfuscatorService::class, function ($app) {
            return new ObfuscatorService();
        });
        
        $this->app->alias(ObfuscatorService::class, 'obfuscator');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration file
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-obfuscator.php' => config_path('laravel-obfuscator.php'),
            ], 'laravel-obfuscator-config');
            
            // Publish views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/laravel-obfuscator'),
            ], 'laravel-obfuscator-views');
            
            // Register console commands
            $this->commands([
                            ObfuscateCommand::class,           // obfuscate:file
            ObfuscateAllCommand::class,        // obfuscate:all
            ObfuscateDirectoryCommand::class,  // obfuscate:directory
            RestoreCommand::class,             // obfuscate:restore
            DeobfuscateCommand::class,         // obfuscate:deobfuscate
            ScheduledObfuscationCommand::class, // obfuscate:scheduled
            ]);
        }
        
        // Load views for web interface
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-obfuscator');
        
        // Load web and API routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }
    
    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [ObfuscatorService::class, 'obfuscator'];
    }
}
