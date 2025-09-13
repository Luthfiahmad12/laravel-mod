<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeModuleCommand extends Command
{
    protected $signature = 'mod:make {name}';
    protected $description = 'Generate a new module';

    public function handle(): int
    {
        $input = $this->argument('name');

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
        $folders = [
            'Http/Controllers',
            'Livewire',
            'Http/Requests',
            'Models',
            'Services',
            'Providers',
            'Routes',
            'Views',
            'Migrations',
        ];

        foreach ($folders as $folder) {
            File::ensureDirectoryExists($modulePath . '/' . $folder, 0755);
        }

        // Placeholder replacements
        $replacements = [
            '{{ModuleName}}' => $studly,
            '{{ModuleNameKebab}}' => $kebab,
            '{{ModuleNameSnake}}' => $snake,
            '{{ModuleNameSnakePlural}}' => $snakePlural,
            '{{ModuleNamespace}}' => "App\\Modules\\{$studly}",
        ];

        $stubs = [
            'controller.stub'       => "Http/Controllers/{$studly}Controller.php",
            'model.stub'            => "Models/{$studly}.php",
            'migration.stub'        => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
            'request.stub'          => "Http/Requests/{$studly}Request.php",
            'service.stub'          => "Services/{$studly}Service.php",
            'service-provider.stub'  => "Providers/{$studly}ServiceProvider.php",
            'route.stub'            => "Routes/web.php",
            'view.stub'             => "Views/index.blade.php",
            'livewire.stub'         => "Livewire/{$studly}Component.php",
            'view-livewire.stub'   => "Views/livewire/{$kebab}-component.blade.php",
        ];

        $stubPath = __DIR__ . '/../../stubs/';

        foreach ($stubs as $stub => $target) {
            if ($stub === 'livewire.stub' && !class_exists('Livewire\\Component')) {
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
        $this->info("✅ Module <comment>{$studly}</comment> generated successfully!");
        $this->newLine();
        return self::SUCCESS;
    }
}
