<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeModuleCommand extends Command
{
    protected $signature = 'mod:make {name} {--api : Generate API resources instead of web resources}';
    protected $description = 'Generate a new module with optional API resources';

    public function handle(): int
    {
        $input = $this->argument('name');
        $isApi = $this->option('api');

        // Cek dependensi API jika flag --api digunakan
        if ($isApi && !$this->checkApiDependencies()) {
            return self::INVALID;
        }

        $studly = Str::studly($input);
        $kebab = Str::kebab($studly);
        $snake = Str::snake($studly);
        $snakePlural = Str::snake(Str::plural($studly));

        $modulePath = base_path("modules/{$studly}");

        if (File::exists($modulePath)) {
            $this->error("Module {$studly} already exists!");
            return self::INVALID;
        }

        // Buat struktur folder
        $folders = $isApi ? [
            'Http/Controllers/Api',
            'Http/Requests',
            'Models',
            'Services',
            'Providers',
            'Routes',
            'Migrations',
            'Views', // Tambahkan Views untuk API juga
        ] : [
            'Http/Controllers',
            'Http/Requests',
            'Models',
            'Services',
            'Providers',
            'Routes',
            'Migrations',
            'Views',
            'Livewire',
        ];

        foreach ($folders as $folder) {
            File::ensureDirectoryExists($modulePath . '/' . $folder, 0755);
        }

        // Placeholder replacements
        $replacements = [
            '{{ModuleName}}' => $studly,
            '{{moduleName}}' => Str::camel($studly),
            '{{ModuleNameKebab}}' => $kebab,
            '{{ModuleNameSnake}}' => $snake,
            '{{ModuleNameSnakePlural}}' => $snakePlural,
            '{{ModuleNamespace}}' => "App\\Modules\\{$studly}",
        ];

        $stubs = $isApi ? [
            'api-controller.stub'   => "Http/Controllers/Api/{$studly}Controller.php",
            'model.stub'            => "Models/{$studly}.php",
            'migration.stub'        => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
            'request.stub'          => "Http/Requests/{$studly}Request.php",
            'service.stub'          => "Services/{$studly}Service.php",
            'service-provider.stub' => "Providers/{$studly}ServiceProvider.php",
            'api-route.stub'        => "Routes/api-{$kebab}.php",
            'view.stub'             => "Views/index.blade.php",
        ] : [
            'controller.stub'       => "Http/Controllers/{$studly}Controller.php",
            'model.stub'            => "Models/{$studly}.php",
            'migration.stub'        => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
            'request.stub'          => "Http/Requests/{$studly}Request.php",
            'service.stub'          => "Services/{$studly}Service.php",
            'service-provider.stub' => "Providers/{$studly}ServiceProvider.php",
            'route.stub'            => "Routes/web-{$kebab}.php",
            'view.stub'             => "Views/index.blade.php",
        ];

        // Tambahkan Livewire stubs jika bukan API dan Livewire tersedia
        if (!$isApi && class_exists('Livewire\Component')) {
            $stubs['livewire.stub'] = "Livewire/{$studly}Component.php";
            $stubs['view-livewire.stub'] = "Views/livewire/{$kebab}-component.blade.php";
        }

        $stubPath = __DIR__ . '/../../stubs/';

        foreach ($stubs as $stub => $target) {
            // Skip Livewire for API modules
            if ($isApi && (strpos($stub, 'livewire') !== false)) {
                continue;
            }

            // Skip Livewire if not installed
            if (!$isApi && $stub === 'livewire.stub' && !class_exists('Livewire\Component')) {
                $this->warn("⚠️ Livewire not installed. Skipping {$studly}Component.");
                continue;
            }

            $source = $stubPath . $stub;
            $targetPath = $modulePath . '/' . $target;

            if (!File::exists($source)) {
                $this->warn("⚠️ Stub not found: {$stub}");
                continue;
            }

            $content = str_replace(array_keys($replacements), array_values($replacements), File::get($source));
            File::put($targetPath, $content);
            $this->line("  └── 📄 <info>{$target}</info>");
        }

        $this->newLine();
        $this->info("✅ Module <comment>{$studly}</comment> generated successfully!" . ($isApi ? " (API)" : ""));
        $this->newLine();
        return self::SUCCESS;
    }

    /**
     * Check if required API dependencies are installed
     */
    protected function checkApiDependencies(): bool
    {
        // Check for Laravel Sanctum
        if (class_exists('Laravel\Sanctum\Sanctum')) {
            $this->info("✓ Sanctum detected");
            return true;
        }

        // Check for Laravel Passport
        if (class_exists('Laravel\Passport\Passport')) {
            $this->info("✓ Passport detected");
            return true;
        }

        // Check for Tymon JWT Auth
        if (class_exists('Tymon\JWTAuth\JWTAuth')) {
            $this->info("✓ JWT Auth detected");
            return true;
        }

        // Check for Laravel Airlock (older version of Sanctum)
        if (class_exists('Laravel\Airlock\Airlock')) {
            $this->info("✓ Airlock detected");
            return true;
        }

        $this->error("API deps not found. Install:");
        $this->line("  - Sanctum: composer require laravel/sanctum");
        $this->line("  - Passport: composer require laravel/passport");
        $this->line("  - JWT: composer require tymon/jwt-auth");
        $this->newLine();
        $this->line("Note: Sanctum is recommended.");

        return false;
    }
}
