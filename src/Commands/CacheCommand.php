<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class CacheCommand extends Command
{
    protected $signature = 'mod:cache {--clear : Clear module caches}';
    protected $description = 'Manage module caches';

    public function handle(): int
    {
        if ($this->option('clear')) {
            $this->info('Clearing module caches...');
            
            // Clear module cache
            Cache::forget('laravel-mod.modules');
            $this->line('✓ Module list cache cleared!');
            
            Cache::forget('laravel-mod.view-namespaces');
            $this->line('✓ View namespace cache cleared!');
            
            Cache::forget('laravel-mod.route-paths');
            $this->line('✓ Route path cache cleared!');
            
            $this->info('Module caches cleared successfully!');
        } else {
            $this->info('Caching module information...');
            
            // Cache module list
            $modules = $this->getActiveModules();
            Cache::forever('laravel-mod.modules', $modules);
            $this->line('✓ Module list cached!');
            
            // Cache view namespaces
            $viewNamespaces = $this->getViewNamespaces($modules);
            Cache::forever('laravel-mod.view-namespaces', $viewNamespaces);
            $this->line('✓ View namespaces cached!');
            
            // Cache route paths
            $routePaths = $this->getRoutePaths($modules);
            Cache::forever('laravel-mod.route-paths', $routePaths);
            $this->line('✓ Route paths cached!');
            
            $this->info('Module caches generated successfully!');
        }

        return self::SUCCESS;
    }
    
    protected function getActiveModules(): array
    {
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
    
    protected function getViewNamespaces(array $modules): array
    {
        $namespaces = [];
        $modulesPath = base_path('modules');
        
        foreach ($modules as $module) {
            $viewsPath = "{$modulesPath}/{$module}/Views";
            if (is_dir($viewsPath)) {
                $namespaces[strtolower($module)] = $viewsPath;
            }
        }
        
        return $namespaces;
    }
    
    protected function getRoutePaths(array $modules): array
    {
        $routes = [];
        $modulesPath = base_path('modules');
        
        foreach ($modules as $module) {
            $routesPath = "{$modulesPath}/{$module}/Routes";
            if (is_dir($routesPath)) {
                $routes[$module] = [];
                foreach (File::files($routesPath) as $file) {
                    if ($file->getExtension() === 'php') {
                        $routes[$module][] = $file->getPathname();
                    }
                }
            }
        }
        
        return $routes;
    }
}