<?php

namespace LaravelMod\Tests\Feature;

use LaravelMod\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DeleteEntityCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create modules directory if not exists
        $modulesPath = base_path('modules');
        if (!File::exists($modulesPath)) {
            File::makeDirectory($modulesPath, 0755, true);
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up created modules
        $testModulePath = base_path('modules/TestModule');
        if (File::exists($testModulePath)) {
            File::deleteDirectory($testModulePath);
        }
        
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_delete_entity_from_existing_module()
    {
        // Create module and entity first
        $this->artisan('mod:make', ['name' => 'TestModule'])
            ->assertExitCode(0);
            
        $this->artisan('mod:make-entity', ['module' => 'TestModule', 'name' => 'Post'])
            ->assertExitCode(0);
        
        // Assert entity exists
        $this->assertTrue(file_exists(base_path('modules/TestModule/Models/Post.php')));
        $this->assertTrue(file_exists(base_path('modules/TestModule/Http/Controllers/PostController.php')));
        
        // Mock confirmation and delete entity
        $this->artisan('mod:delete-entity', ['module' => 'TestModule', 'entity' => 'Post'])
            ->expectsConfirmation('Are you sure you want to delete the entity Post from module TestModule? This action cannot be undone.', 'yes')
            ->expectsOutput('Entity Post deleted successfully from module TestModule!')
            ->assertExitCode(0);
            
        // Assert entity files are deleted
        $this->assertFalse(file_exists(base_path('modules/TestModule/Models/Post.php')));
        $this->assertFalse(file_exists(base_path('modules/TestModule/Http/Controllers/PostController.php')));
        $this->assertFalse(file_exists(base_path('modules/TestModule/Http/Requests/PostRequest.php')));
    }
    
    /** @test */
    public function it_cannot_delete_entity_from_non_existent_module()
    {
        $this->artisan('mod:delete-entity', ['module' => 'NonExistentModule', 'entity' => 'Post'])
            ->expectsOutput('Module NonExistentModule does not exist!')
            ->assertExitCode(2); // Changed to 2 which is self::INVALID
    }
}