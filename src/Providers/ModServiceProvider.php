<?php

namespace LaravelMod\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelMod\Commands\MakeModuleCommand;

class ModServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            MakeModuleCommand::class,
        ]);
    }

    public function boot(): void
    {
        $modulesPath = base_path('modules');

        if (is_dir($modulesPath)) {
            foreach (scandir($modulesPath) as $module) {
                if ($module === '.' || $module === '..') continue;

                $provider = "App\\Modules\\{$module}\\Providers\\{$module}ServiceProvider";

                if (class_exists($provider)) {
                    $this->app->register($provider);
                }
            }
        }
    }
}
