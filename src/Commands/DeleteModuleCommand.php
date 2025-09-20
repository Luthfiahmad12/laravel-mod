<?php

namespace LaravelMod\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DeleteModuleCommand extends Command
{
    protected $signature = 'mod:delete-module {name}';
    protected $description = 'Delete an existing module';

    public function handle(): int
    {
        $name = $this->argument('name');
        $studly = ucfirst($name);
        $modulePath = base_path("modules/{$studly}");

        // Validate module exists
        if (!File::exists($modulePath)) {
            $this->fail("Module {$studly} does not exist!");
            return self::INVALID;
        }

        // Confirm deletion
        if (!$this->confirm("Are you sure you want to delete the module {$studly}? This action cannot be undone.")) {
            $this->info("Module deletion cancelled.");
            return self::SUCCESS;
        }

        // Delete module directory
        File::deleteDirectory($modulePath);

        $this->info("Module {$studly} deleted successfully!");

        return self::SUCCESS;
    }
}