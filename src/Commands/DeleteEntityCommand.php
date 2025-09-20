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
            $this->error("Module {$moduleStudly} does not exist!");
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
        
        // Delete route files
        $webRoutePath = "{$modulePath}/Routes/web-{$entityKebab}.php";
        if (File::exists($webRoutePath)) {
            File::delete($webRoutePath);
            $deletedFiles[] = "Routes/web-{$entityKebab}.php";
        }
        
        $apiRoutePath = "{$modulePath}/Routes/api-{$entityKebab}.php";
        if (File::exists($apiRoutePath)) {
            File::delete($apiRoutePath);
            $deletedFiles[] = "Routes/api-{$entityKebab}.php";
        }
        
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
        
        if (empty($deletedFiles)) {
            $this->warn("No files found for entity {$entityStudly} in module {$moduleStudly}.");
        } else {
            $this->info("✅ Entity <comment>{$entityStudly}</comment> deleted successfully from module <comment>{$moduleStudly}</comment>!");
            foreach ($deletedFiles as $file) {
                $this->line("  └── 📄 <info>{$file}</info>");
            }
        }
        
        return self::SUCCESS;
    }
}