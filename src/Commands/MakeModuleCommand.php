<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeModuleCommand extends Command
{
    protected $signature = 'mod:make {name} {--api : Generate API resources in addition to web resources}';
    protected $description = 'Generate a new module with optional API resources in addition to web resources';

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
        $snakePlural = Str::snake(Str::plural($studly)); // Untuk migration
        $kebabPlural = Str::kebab(Str::plural($studly)); // Untuk view folder

        $modulePath = base_path("modules/{$studly}");

        if (File::exists($modulePath)) {
            $this->fail("Module {$studly} already exists!");
            return self::INVALID;
        }

        // Buat struktur folder
        // Untuk API module, tetap buat folder web controller juga
        $folders = $isApi ? [
            'Http/Controllers', // Tambahkan folder web controller
            'Http/Controllers/Api',
            'Http/Requests',
            'Models',
            'Services',
            'Providers',
            'Routes',
            'Migrations',
            'Views', // Tambahkan Views untuk API juga
            // Tambahkan Livewire folder jika Livewire tersedia
            class_exists('Livewire\Component') ? 'Livewire' : null,
        ] : [
            'Http/Controllers',
            'Http/Requests',
            'Models',
            'Services',
            'Providers',
            'Routes',
            'Migrations',
            'Views',
            // Tambahkan Livewire folder jika Livewire tersedia
            class_exists('Livewire\Component') ? 'Livewire' : null,
        ];

        // Filter null values
        $folders = array_filter($folders);

        foreach ($folders as $folder) {
            File::ensureDirectoryExists($modulePath . '/' . $folder, 0755);
        }

        // Placeholder replacements
        $replacements = [
            '{{ModuleName}}' => $studly,
            '{{moduleName}}' => Str::camel($studly),
            '{{ModuleNameKebab}}' => $kebab,
            '{{ModuleNameKebabPlural}}' => $kebabPlural, // Tambahkan untuk view folder
            '{{ModuleNameSnake}}' => $snake,
            '{{ModuleNameSnakePlural}}' => $snakePlural,
            '{{ModuleNamespace}}' => "App\Modules\{$studly}",
        ];

        // Untuk API module, tetap buat controller dan route web juga
        $stubs = $isApi ? [
            'controller.stub'       => "Http/Controllers/{$studly}Controller.php", // Tambahkan web controller
            'api-controller.stub'   => "Http/Controllers/Api/{$studly}Controller.php",
            'model.stub'            => "Models/{$studly}.php",
            'migration.stub'        => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
            'request.stub'          => "Http/Requests/{$studly}Request.php",
            'service.stub'          => "Services/{$studly}Service.php",
            'service-provider.stub' => "Providers/{$studly}ServiceProvider.php",
            'route.stub'            => "Routes/web.php", // Nama file lebih sederhana
            'api-route.stub'        => "Routes/api.php", // Nama file lebih sederhana
            'view.stub'             => "Views/{$kebabPlural}/index.blade.php", // View dalam folder plural yang benar
        ] : [
            'controller.stub'       => "Http/Controllers/{$studly}Controller.php",
            'model.stub'            => "Models/{$studly}.php",
            'migration.stub'        => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
            'request.stub'          => "Http/Requests/{$studly}Request.php",
            'service.stub'          => "Services/{$studly}Service.php",
            'service-provider.stub' => "Providers/{$studly}ServiceProvider.php",
            'route.stub'            => "Routes/web.php", // Nama file lebih sederhana
            'view.stub'             => "Views/{$kebabPlural}/index.blade.php", // View dalam folder plural yang benar
        ];

        // Tambahkan Livewire stubs jika Livewire tersedia
        if (class_exists('Livewire\Component')) {
            $stubs['livewire.stub'] = "Livewire/{$studly}Component.php";
            $stubs['view-livewire.stub'] = "Views/livewire/{$kebab}-component.blade.php";
        }

        $stubPath = __DIR__ . '/../../stubs/';

        foreach ($stubs as $stub => $target) {
            // Skip Livewire for API modules if Livewire is not installed
            if ($isApi && (strpos($stub, 'livewire') !== false) && !class_exists('Livewire\Component')) {
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

            // Ensure target directory exists before writing
            $targetDir = dirname($targetPath);
            if (!File::exists($targetDir)) {
                File::ensureDirectoryExists($targetDir, 0755);
            }

            // Write file content
            if (File::put($targetPath, $content) === false) {
                $this->warn("⚠️ Failed to create file: {$target}");
                continue;
            }
            $this->line("  - <info>{$target}</info>");
        }

        $this->newLine();
        $this->info("Module {$studly} generated successfully!" . ($isApi ? " (API)" : ""));
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Check if required API dependencies are installed
     */
    protected function checkApiDependencies(): bool
    {
        // Check for Laravel Sanctum (included by default in Laravel 9+)
        if (class_exists('Laravel\Sanctum\Sanctum')) {
            $this->info("✓ Sanctum detected (included with Laravel 9+)");
            return true;
        }

        // Check for Laravel Passport
        if (class_exists('Laravel\Passport\Passport')) {
            $this->info("✓ Passport detected");
            return true;
        }

        // Check for Tymon JWT Auth
        if (class_exists('Tymon\JWTAuth\Providers\LaravelServiceProvider') || class_exists('Tymon\JWTAuth\JWTAuth')) {
            $this->info("✓ JWT Auth detected");
            return true;
        }

        // Check for Laravel Airlock (older version of Sanctum)
        if (class_exists('Laravel\Airlock\Airlock')) {
            $this->info("✓ Airlock detected (legacy)");
            return true;
        }

        $this->fail("API authentication package not found. Skipping");

        return false;
    }
}
