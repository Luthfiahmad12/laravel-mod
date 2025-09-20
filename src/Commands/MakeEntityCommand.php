<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeEntityCommand extends Command
{
    protected $signature = 'mod:make-entity {module} {name} {--api : Generate API resources instead of web resources}';
    protected $description = 'Generate a new entity (with all components) within an existing module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $isApi = $this->option('api');

        // Validate module exists
        $modulePath = base_path("modules/{$module}");
        if (!File::exists($modulePath)) {
            $this->error("Module {$module} does not exist!");
            return self::INVALID;
        }

        // Check if API module
        $isApiModule = File::exists($modulePath . '/Http/Controllers/Api');
        
        // Validate API flag consistency
        if ($isApi && !$isApiModule) {
            $this->error("Module {$module} is not an API module. Please remove the --api flag or create an API module first.");
            return self::INVALID;
        }
        
        if (!$isApi && $isApiModule) {
            $this->error("Module {$module} is an API module. Please use the --api flag.");
            return self::INVALID;
        }

        // Process naming
        $studly = Str::studly($name);
        $kebab = Str::kebab($studly);
        $snake = Str::snake($studly);
        $snakePlural = Str::snake(Str::plural($studly));

        // Check if entity already exists (main model)
        $modelPath = $modulePath . '/Models/' . $studly . '.php';
        if (File::exists($modelPath)) {
            $this->error("Entity {$studly} already exists in module {$module}!");
            return self::INVALID;
        }

        // Prepare replacements
        $replacements = [
            '{{ModuleName}}' => $studly,
            '{{moduleName}}' => Str::camel($studly),
            '{{ModuleNameKebab}}' => $kebab,
            '{{ModuleNameSnake}}' => $snake,
            '{{ModuleNameSnakePlural}}' => $snakePlural,
            '{{ModuleNamespace}}' => "App\\Modules\\{$module}",
        ];

        // Get stub path
        $stubPath = __DIR__ . '/../../stubs/';
        
        // Define files to generate based on module type
        $files = $isApi ? [
            'model.stub' => "Models/{$studly}.php",
            'request.stub' => "Http/Requests/{$studly}Request.php",
            'api-controller.stub' => "Http/Controllers/Api/{$studly}Controller.php",
            'api-route.stub' => "Routes/api-{$kebab}.php",
            'view.stub' => "Views/index.blade.php",
            'migration.stub' => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
        ] : [
            'model.stub' => "Models/{$studly}.php",
            'request.stub' => "Http/Requests/{$studly}Request.php",
            'controller.stub' => "Http/Controllers/{$studly}Controller.php",
            'route.stub' => "Routes/web-{$kebab}.php",
            'view.stub' => "Views/index.blade.php",
            'migration.stub' => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
        ];

        // Add Livewire components if not API and Livewire is available
        if (!$isApi && class_exists('Livewire\Component')) {
            $files['livewire.stub'] = "Livewire/{$studly}Component.php";
            $files['view-livewire.stub'] = "Views/livewire/{$kebab}-component.blade.php";
        }

        // Generate files
        $generatedFiles = [];
        foreach ($files as $stub => $target) {
            $source = $stubPath . $stub;
            
            // Check if it's a web-only stub for API modules
            if ($isApi && (strpos($stub, 'view') !== false || strpos($stub, 'livewire') !== false)) {
                continue;
            }
            
            // Check if it's a Livewire stub but Livewire not available
            if (strpos($stub, 'livewire') !== false && !class_exists('Livewire\Component')) {
                continue;
            }
            
            if (!File::exists($source)) {
                $this->warn("⚠️ Stub not found: {$stub}");
                continue;
            }

            // Ensure directory exists
            $targetDir = dirname($modulePath . '/' . $target);
            if (!File::exists($targetDir)) {
                File::ensureDirectoryExists($targetDir, 0755);
            }

            $content = str_replace(array_keys($replacements), array_values($replacements), File::get($source));
            File::put($modulePath . '/' . $target, $content);
            $generatedFiles[] = $target;
        }

        // Output results
        $this->info("✅ Entity <comment>{$studly}</comment> created successfully in module <comment>{$module}</comment>!" . ($isApi ? " (API)" : ""));
        foreach ($generatedFiles as $file) {
            $this->line("  └── 📄 <info>{$file}</info>");
        }

        return self::SUCCESS;
    }
}