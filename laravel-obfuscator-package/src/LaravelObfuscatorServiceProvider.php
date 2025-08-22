<?php

namespace LaravelObfuscator\LaravelObfuscator;

use Illuminate\Support\ServiceProvider;
use LaravelObfuscator\LaravelObfuscator\Services\ObfuscatorService;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\ObfuscateCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\ObfuscateAllCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\ObfuscateDirectoryCommand;
use LaravelObfuscator\LaravelObfuscator\Console\Commands\RestoreCommand;

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
            
            // Register console commands
            $this->commands([
                ObfuscateCommand::class,           // mObfuscate:file
                ObfuscateAllCommand::class,        // mObfuscate:all
                ObfuscateDirectoryCommand::class,  // mObfuscate:directory
                RestoreCommand::class,             // mObfuscate:restore
            ]);
        }
        
        // Load views if you want to provide a web interface
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-obfuscator');
        
        // Load routes if you want to provide web routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }
    
    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [ObfuscatorService::class, 'obfuscator'];
    }
}
