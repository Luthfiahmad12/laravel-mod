<?php

namespace LaravelMod\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use LaravelMod\Commands\MakeModuleCommand;
use LaravelMod\Commands\MakeEntityCommand;
use LaravelMod\Commands\DeleteModuleCommand;
use LaravelMod\Commands\CacheCommand;
use LaravelMod\Commands\DeleteEntityCommand;

class ModServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MakeModuleCommand::class,
            MakeEntityCommand::class,
            DeleteModuleCommand::class,
            CacheCommand::class,
            DeleteEntityCommand::class,
        ]);
    }

    public function boot(): void
    {
        // Load modules from cache or scan directory
        $modules = $this->getActiveModules();
        
        foreach ($modules as $module) {
            $modulePath = base_path("modules/{$module}/");
            
            // Load routes directly
            $this->loadModuleRoutes($modulePath, $module);
            
            // Load views directly  
            $this->loadModuleViews($modulePath, $module);
            
            // Load migrations directly
            $this->loadModuleMigrations($modulePath);
            
            // Load Livewire components if Livewire is available
            $this->loadModuleLivewireComponents($modulePath, $module);
            
            // Register module service provider if exists
            $provider = "App\\Modules\\{$module}\\Providers\\{$module}ServiceProvider";
            if (class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }
    
    protected function getActiveModules(): array
    {
        // Try to get from cache first
        $cached = Cache::get('laravel-mod.modules');
        if ($cached !== null) {
            return $cached;
        }
        
        // Fallback to directory scan
        $modulesPath = base_path('modules');
        $modules = [];
        
        if (is_dir($modulesPath)) {
            foreach (scandir($modulesPath) as $module) {
                if ($module === '.' || $module === '..') continue;
                if (is_dir("{$modulesPath}/{$module}")) {
                    $modules[] = $module;
                }
            }
        }
        
        return $modules;
    }
    
    protected function loadModuleRoutes(string $modulePath, string $module): void
    {
        $routesPath = $modulePath . 'Routes/';
        
        if (File::isDirectory($routesPath)) {
            // Try cache first
            $cachedRoutes = Cache::get('laravel-mod.route-paths', []);
            $routeFiles = $cachedRoutes[$module] ?? [];
            
            // If no cache, scan directory
            if (empty($routeFiles)) {
                foreach (File::files($routesPath) as $file) {
                    if ($file->getExtension() === 'php') {
                        $routeFiles[] = $file->getPathname();
                    }
                }
            }
            
            // Load route files
            foreach ($routeFiles as $routeFile) {
                $filename = basename($routeFile);
                if (strpos($filename, 'web-') === 0) {
                    $this->loadRoutesFrom($routeFile, ['middleware' => 'web']);
                } elseif (strpos($filename, 'api-') === 0) {
                    $this->loadRoutesFrom($routeFile, ['middleware' => 'api']);
                }
            }
        }
    }
    
    protected function loadModuleViews(string $modulePath, string $module): void
    {
        $views = $modulePath . 'Views';
        if (File::isDirectory($views)) {
            // Try cache first
            $cachedNamespaces = Cache::get('laravel-mod.view-namespaces', []);
            if (isset($cachedNamespaces[strtolower($module)])) {
                $this->loadViewsFrom($cachedNamespaces[strtolower($module)], strtolower($module));
            } else {
                $this->loadViewsFrom($views, strtolower($module));
            }
        }
    }
    
    protected function loadModuleMigrations(string $modulePath): void
    {
        $migrations = $modulePath . 'Migrations';
        if (File::isDirectory($migrations)) {
            $this->loadMigrationsFrom($migrations);
        }
    }
    
    protected function loadModuleLivewireComponents(string $modulePath, string $module): void
    {
        // Check if Livewire is installed
        if (!class_exists('Livewire\Component')) {
            return;
        }
        
        $livewirePath = $modulePath . 'Livewire';
        if (File::isDirectory($livewirePath)) {
            foreach (File::files($livewirePath) as $file) {
                if ($file->getExtension() === 'php') {
                    $componentClass = "App\\Modules\\{$module}\\Livewire\\" . $file->getBasename('.php');
                    $componentAlias = strtolower($module) . '-' . strtolower($file->getBasename('.php'));
                    
                    // Check if class exists before registering
                    if (class_exists($componentClass)) {
                        // Register with Livewire
                        \Livewire\Livewire::component($componentAlias, $componentClass);
                    }
                }
            }
        }
    }
}

