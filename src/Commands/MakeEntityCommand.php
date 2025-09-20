<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MakeEntityCommand extends Command
{
    protected $signature = 'mod:make-entity {module} {name} {--api : Generate API resources in addition to web resources}';
    protected $description = 'Generate a new entity (with all components) within an existing module with optional API resources';

    public function handle(): int
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $isApi = $this->option('api');

        // Validate module exists
        $modulePath = base_path("modules/{$module}");
        if (!File::exists($modulePath)) {
            $this->fail("Module {$module} does not exist!");
            return self::INVALID;
        }

        // Process naming
        $studly = Str::studly($name);
        $kebab = Str::kebab($studly);
        $snake = Str::snake($studly);
        $snakePlural = Str::snake(Str::plural($studly)); // Untuk migration
        $kebabPlural = Str::kebab(Str::plural($studly)); // Untuk view folder

        // Check if entity already exists (main model)
        $modelPath = $modulePath . '/Models/' . $studly . '.php';
        if (File::exists($modelPath)) {
            $this->fail("Entity {$studly} already exists in module {$module}!");
            return self::INVALID;
        }

        // Prepare replacements
        $replacements = [
            '{{ModuleName}}' => $studly,
            '{{moduleName}}' => Str::camel($studly),
            '{{ModuleNameKebab}}' => $kebab,
            '{{ModuleNameKebabPlural}}' => $kebabPlural, // Tambahkan untuk view folder
            '{{ModuleNameSnake}}' => $snake,
            '{{ModuleNameSnakePlural}}' => $snakePlural,
            '{{ModuleNamespace}}' => "App\Modules\{{$module}}",
        ];

        // Get stub path
        $stubPath = __DIR__ . '/../../stubs/';
        
        // Define files to generate based on module type
        // Untuk API module, tetap buat controller dan route web juga
        $files = $isApi ? [
            'model.stub' => "Models/{$studly}.php",
            'request.stub' => "Http/Requests/{$studly}Request.php",
            'controller.stub' => "Http/Controllers/{$studly}Controller.php", // Tambahkan web controller
            'api-controller.stub' => "Http/Controllers/Api/{$studly}Controller.php",
            'view.stub' => "Views/{$kebabPlural}/index.blade.php", // View dalam folder plural yang benar
            'migration.stub' => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
            'service.stub' => "Services/{$studly}Service.php",
        ] : [
            'model.stub' => "Models/{$studly}.php",
            'request.stub' => "Http/Requests/{$studly}Request.php",
            'controller.stub' => "Http/Controllers/{$studly}Controller.php",
            'view.stub' => "Views/{$kebabPlural}/index.blade.php", // View dalam folder plural yang benar
            'migration.stub' => "Migrations/" . date('Y_m_d_His') . "_create_{$snakePlural}_table.php",
            'service.stub' => "Services/{$studly}Service.php",
        ];

        // Add Livewire components if Livewire is available
        if (class_exists('Livewire\Component')) {
            // Ensure Livewire directory exists
            $livewireDir = $modulePath . '/Livewire';
            if (!File::exists($livewireDir)) {
                File::ensureDirectoryExists($livewireDir, 0755);
            }
            
            $files['livewire.stub'] = "Livewire/{$studly}Component.php";
            $files['view-livewire.stub'] = "Views/livewire/{$kebab}-component.blade.php";
        }

        // Generate files
        $generatedFiles = [];
        foreach ($files as $stub => $target) {
            $source = $stubPath . $stub;
            
            // Check if it's a Livewire stub but Livewire not available
            if (strpos($stub, 'livewire') !== false && !class_exists('Livewire\Component')) {
                $this->warn("⚠️ Livewire not installed. Skipping {$studly}Component.");
                continue;
            }
            
            if (!File::exists($source)) {
                $this->warn("⚠️ Stub not found: {$stub}");
                continue;
            }

            $content = str_replace(array_keys($replacements), array_values($replacements), File::get($source));
            
            // Ensure target directory exists before writing
            $targetDir = dirname($modulePath . '/' . $target);
            if (!File::exists($targetDir)) {
                File::ensureDirectoryExists($targetDir, 0755);
            }
            
            // Write file content
            if (File::put($modulePath . '/' . $target, $content) === false) {
                $this->warn("⚠️ Failed to create file: {$target}");
                continue;
            }
            $generatedFiles[] = $target;
        }

        // Output results
        $this->info("Entity {$studly} created successfully in module {$module}!" . ($isApi ? " (API)" : ""));
        foreach ($generatedFiles as $file) {
            $this->line("  - <info>{$file}</info>");
        }

        // Add entity route to existing route files
        $this->addEntityRoute($modulePath, $studly, $kebab, $isApi);

        return self::SUCCESS;
    }

    /**
     * Add entity route to existing route files
     */
    protected function addEntityRoute(string $modulePath, string $studly, string $kebab, bool $isApi): void
    {
        // For web routes
        $webRoutePath = $modulePath . '/Routes/web.php';
        if (File::exists($webRoutePath)) {
            $webRouteContent = File::get($webRoutePath);
            
            // Check if route already exists
            if (strpos($webRouteContent, "Route::get('/{$kebab}'") === false) {
                // Add use statement for the entity controller
                $useStatement = "use App\\Modules\\" . basename($modulePath) . "\\Http\\Controllers\\{$studly}Controller;
";
                // Check if use statement already exists
                if (strpos($webRouteContent, $useStatement) === false) {
                    // Find the position to insert the use statement (after the last use statement)
                    $lastUsePosition = strrpos($webRouteContent, "use ");
                    if ($lastUsePosition !== false) {
                        // Find the end of the use statement line
                        $endOfUseLine = strpos($webRouteContent, ";
", $lastUsePosition);
                        if ($endOfUseLine !== false) {
                            $webRouteContent = substr_replace($webRouteContent, $useStatement, $endOfUseLine + 2, 0);
                        }
                    }
                }
                
                // Find the position to insert the new route (before the comment or at the end)
                $insertPosition = strrpos($webRouteContent, '// Entity routes will be added here');
                if ($insertPosition !== false) {
                    $newRoute = "Route::get('/{$kebab}', [{$studly}Controller::class, 'index'])->name('{$kebab}.index');
";
                    $webRouteContent = substr_replace($webRouteContent, $newRoute, $insertPosition, 0);
                    File::put($webRoutePath, $webRouteContent);
                    $this->line("  - <info>Added route to web.php</info>");
                } else {
                    // Fallback: add before the closing PHP tag
                    $insertPosition = strrpos($webRouteContent, '?>');
                    if ($insertPosition !== false) {
                        $newRoute = "
Route::get('/{$kebab}', [{$studly}Controller::class, 'index'])->name('{$kebab}.index');
";
                        $webRouteContent = substr_replace($webRouteContent, $newRoute, $insertPosition, 0);
                        File::put($webRoutePath, $webRouteContent);
                        $this->line("  - <info>Added route to web.php</info>");
                    }
                }
            }
        }

        // For API routes
        if ($isApi) {
            $apiRoutePath = $modulePath . '/Routes/api.php';
            if (File::exists($apiRoutePath)) {
                $apiRouteContent = File::get($apiRoutePath);
                
                // Check if route already exists
                if (strpos($apiRouteContent, "Route::get('/{$kebab}'") === false) {
                    // Add use statement for the entity controller
                    $useStatement = "use App\\Modules\\" . basename($modulePath) . "\\Http\\Controllers\\{$studly}Controller;
";
                    // Check if use statement already exists
                    if (strpos($apiRouteContent, $useStatement) === false) {
                        // Find the position to insert the use statement (after the last use statement)
                        $lastUsePosition = strrpos($apiRouteContent, "use ");
                        if ($lastUsePosition !== false) {
                            // Find the end of the use statement line
                            $endOfUseLine = strpos($apiRouteContent, ";
", $lastUsePosition);
                            if ($endOfUseLine !== false) {
                                $apiRouteContent = substr_replace($apiRouteContent, $useStatement, $endOfUseLine + 2, 0);
                            }
                        }
                    }
                    
                    // Find the position to insert the new route (before the comment or at the end)
                    $insertPosition = strrpos($apiRouteContent, '// Entity routes will be added here');
                    if ($insertPosition !== false) {
                        $newRoute = "Route::get('/{$kebab}', [{$studly}Controller::class, 'index'])->name('api.{$kebab}.index');
";
                        $apiRouteContent = substr_replace($apiRouteContent, $newRoute, $insertPosition, 0);
                        File::put($apiRoutePath, $apiRouteContent);
                        $this->line("  - <info>Added route to api.php</info>");
                    } else {
                        // Fallback: add before the closing PHP tag
                        $insertPosition = strrpos($apiRouteContent, '?>');
                        if ($insertPosition !== false) {
                            $newRoute = "
Route::get('/{$kebab}', [{$studly}Controller::class, 'index'])->name('api.{$kebab}.index');
";
                            $apiRouteContent = substr_replace($apiRouteContent, $newRoute, $insertPosition, 0);
                            File::put($apiRoutePath, $apiRouteContent);
                            $this->line("  - <info>Added route to api.php</info>");
                        }
                    }
                }
            }
        }
    }
}