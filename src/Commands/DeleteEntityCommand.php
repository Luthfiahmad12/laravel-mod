<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DeleteEntityCommand extends Command
{
    protected $signature = 'mod:delete-entity {module} {entity}';
    protected $description = 'Delete an entity from an existing module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $entity = $this->argument('entity');
        
        $moduleStudly = Str::studly($module);
        $entityStudly = Str::studly($entity);
        $entityKebab = Str::kebab($entityStudly);
        
        $modulePath = base_path("modules/{$moduleStudly}");
        
        // Validate module exists
        if (!File::exists($modulePath)) {
            $this->line("Module {$moduleStudly} does not exist!");
            return self::INVALID;
        }
        
        // Confirm deletion
        if (!$this->confirm("Are you sure you want to delete the entity {$entityStudly} from module {$moduleStudly}? This action cannot be undone.")) {
            $this->info("Entity deletion cancelled.");
            return self::SUCCESS;
        }
        
        // Delete entity files
        $deletedFiles = [];
        
        // Delete model
        $modelPath = "{$modulePath}/Models/{$entityStudly}.php";
        if (File::exists($modelPath)) {
            File::delete($modelPath);
            $deletedFiles[] = "Models/{$entityStudly}.php";
        }
        
        // Delete controller
        $controllerPath = "{$modulePath}/Http/Controllers/{$entityStudly}Controller.php";
        if (File::exists($controllerPath)) {
            File::delete($controllerPath);
            $deletedFiles[] = "Http/Controllers/{$entityStudly}Controller.php";
        }
        
        // Delete API controller if exists
        $apiControllerPath = "{$modulePath}/Http/Controllers/Api/{$entityStudly}Controller.php";
        if (File::exists($apiControllerPath)) {
            File::delete($apiControllerPath);
            $deletedFiles[] = "Http/Controllers/Api/{$entityStudly}Controller.php";
        }
        
        // Delete request
        $requestPath = "{$modulePath}/Http/Requests/{$entityStudly}Request.php";
        if (File::exists($requestPath)) {
            File::delete($requestPath);
            $deletedFiles[] = "Http/Requests/{$entityStudly}Request.php";
        }
        
        // Delete service
        $servicePath = "{$modulePath}/Services/{$entityStudly}Service.php";
        if (File::exists($servicePath)) {
            File::delete($servicePath);
            $deletedFiles[] = "Services/{$entityStudly}Service.php";
        }
        
        // Delete migration
        $migrationFiles = File::glob("{$modulePath}/Migrations/*{$entityKebab}*");
        foreach ($migrationFiles as $migrationFile) {
            File::delete($migrationFile);
            $deletedFiles[] = "Migrations/" . basename($migrationFile);
        }
        
        // Delete view directory
        $viewPath = "{$modulePath}/Views/{$entityKebab}";
        if (File::exists($viewPath)) {
            File::deleteDirectory($viewPath);
            $deletedFiles[] = "Views/{$entityKebab}/";
        }
        
        // Note: Route files are no longer deleted individually as they are consolidated
        // Entity routes are removed from the main route files instead
        
        // Delete Livewire component if exists
        $livewirePath = "{$modulePath}/Livewire/{$entityStudly}Component.php";
        if (File::exists($livewirePath)) {
            File::delete($livewirePath);
            $deletedFiles[] = "Livewire/{$entityStudly}Component.php";
        }
        
        // Delete Livewire view if exists
        $livewireViewPath = "{$modulePath}/Views/livewire/{$entityKebab}-component.blade.php";
        if (File::exists($livewireViewPath)) {
            File::delete($livewireViewPath);
            $deletedFiles[] = "Views/livewire/{$entityKebab}-component.blade.php";
        }
        
        // Remove entity route from main route files
        $this->removeEntityRoute($modulePath, $entityKebab);
        
        if (empty($deletedFiles)) {
            $this->warn("No files found for entity {$entityStudly} in module {$moduleStudly}.");
        } else {
            $this->info("Entity {$entityStudly} deleted successfully from module {$moduleStudly}!");
            foreach ($deletedFiles as $file) {
                $this->line("  - <info>{$file}</info>");
            }
        }
        
        return self::SUCCESS;
    }

    /**
     * Remove entity route from main route files
     */
    protected function removeEntityRoute(string $modulePath, string $entityKebab): void
    {
        // For web routes
        $webRoutePath = $modulePath . '/Routes/web.php';
        if (File::exists($webRoutePath)) {
            $webRouteContent = File::get($webRoutePath);
            
            // Remove the entity route line
            $pattern = '/Route::get\\(\'\\/' . $entityKebab . '\', \\[.*Controller::class, \'index\'\\]\\)->name\\(\'' . $entityKebab . '\\.index\'\\);\n?/';
            $webRouteContent = preg_replace($pattern, '', $webRouteContent);
            
            File::put($webRoutePath, $webRouteContent);
            $this->line("  - <info>Removed route from web.php</info>");
        }

        // For API routes (if exists)
        $apiRoutePath = $modulePath . '/Routes/api.php';
        if (File::exists($apiRoutePath)) {
            $apiRouteContent = File::get($apiRoutePath);
            
            // Remove the entity route line
            $pattern = '/Route::get\\(\'\\/' . $entityKebab . '\', \\[.*Controller::class, \'index\'\\]\\)->name\\(\'api\\.' . $entityKebab . '\\.index\'\\);\n?/';
            $apiRouteContent = preg_replace($pattern, '', $apiRouteContent);
            
            File::put($apiRoutePath, $apiRouteContent);
            $this->line("  - <info>Removed route from api.php</info>");
        }
    }
}