<?php

namespace LaravelMod\Tests\Feature;

use LaravelMod\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MakeEntityCommandTest extends TestCase
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
        
        $testApiModulePath = base_path('modules/TestApiModule');
        if (File::exists($testApiModulePath)) {
            File::deleteDirectory($testApiModulePath);
        }
        
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_create_entity_in_existing_module()
    {
        // Create module first
        $this->artisan('mod:make', ['name' => 'TestModule'])
            ->assertExitCode(0);
        
        // Create entity
        $this->artisan('mod:make-entity', ['module' => 'TestModule', 'name' => 'Post'])
            ->expectsOutput('Entity Post created successfully in module TestModule!')
            ->assertExitCode(0);
            
        // Assert entity files are created
        $this->assertTrue(file_exists(base_path('modules/TestModule/Models/Post.php')));
        $this->assertTrue(file_exists(base_path('modules/TestModule/Http/Controllers/PostController.php')));
        $this->assertTrue(file_exists(base_path('modules/TestModule/Http/Requests/PostRequest.php')));
        $this->assertTrue(file_exists(base_path('modules/TestModule/Services/PostService.php')));
        // Route is now added to main route file, not created as separate file
        $this->assertTrue(file_exists(base_path('modules/TestModule/Views/posts/index.blade.php')));
    }
    
    /** @test */
    public function it_cannot_create_entity_if_module_does_not_exist()
    {
        $this->artisan('mod:make-entity', ['module' => 'NonExistentModule', 'name' => 'Post'])
            ->expectsOutput('Module NonExistentModule does not exist!')
            ->assertExitCode(2); // Changed to 2 which is self::INVALID
    }
    
    /** @test */
    public function it_creates_api_entity_in_api_module()
    {
        // Create API module first
        // Skip this test if no API dependencies are installed
        if (!class_exists('Laravel\Sanctum\Sanctum') && 
            !class_exists('Laravel\Passport\Passport') && 
            !class_exists('Tymon\JWTAuth\JWTAuth') && 
            !class_exists('Laravel\Airlock\Airlock')) {
            $this->markTestSkipped('No API authentication package installed');
            return;
        }
        
        $this->artisan('mod:make', ['name' => 'TestApiModule', '--api' => true])
            ->assertExitCode(0);
        
        // Create API entity
        $this->artisan('mod:make-entity', ['module' => 'TestApiModule', 'name' => 'Post', '--api' => true])
            ->expectsOutput('Entity Post created successfully in module TestApiModule! (API)')
            ->assertExitCode(0);
            
        // Assert API entity files are created
        $this->assertTrue(file_exists(base_path('modules/TestApiModule/Models/Post.php')));
        $this->assertTrue(file_exists(base_path('modules/TestApiModule/Http/Controllers/PostController.php')));
        $this->assertTrue(file_exists(base_path('modules/TestApiModule/Http/Controllers/Api/PostController.php')));
        $this->assertTrue(file_exists(base_path('modules/TestApiModule/Http/Requests/PostRequest.php')));
        $this->assertTrue(file_exists(base_path('modules/TestApiModule/Services/PostService.php')));
        // Route is now added to main route file, not created as separate file
        $this->assertTrue(file_exists(base_path('modules/TestApiModule/Views/posts/index.blade.php')));
    }
}