<?php

namespace LaravelMod\Tests\Feature;

use LaravelMod\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MakeModuleCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create modules directory if not exists
        if (!File::exists(base_path('modules'))) {
            File::makeDirectory(base_path('modules'));
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up created modules
        if (File::exists(base_path('modules/TestModule'))) {
            File::deleteDirectory(base_path('modules/TestModule'));
        }
        
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_create_a_new_module()
    {
        $this->artisan('mod:make', ['name' => 'TestModule'])
            ->expectsOutput('✅ Module TestModule generated successfully!')
            ->assertExitCode(0);
            
        // Assert module directory is created
        $this->assertDirectoryExists(base_path('modules/TestModule'));
        
        // Assert basic structure
        $this->assertDirectoryExists(base_path('modules/TestModule/Http/Controllers'));
        $this->assertDirectoryExists(base_path('modules/TestModule/Http/Requests'));
        $this->assertDirectoryExists(base_path('modules/TestModule/Models'));
        $this->assertDirectoryExists(base_path('modules/TestModule/Services'));
        $this->assertDirectoryExists(base_path('modules/TestModule/Providers'));
        $this->assertDirectoryExists(base_path('modules/TestModule/Routes'));
        $this->assertDirectoryExists(base_path('modules/TestModule/Views'));
        $this->assertDirectoryExists(base_path('modules/TestModule/Migrations'));
    }
    
    /** @test */
    public function it_cannot_create_module_if_already_exists()
    {
        // Create module first
        $this->artisan('mod:make', ['name' => 'TestModule']);
        
        // Try to create again
        $this->artisan('mod:make', ['name' => 'TestModule'])
            ->expectsOutput('Module TestModule already exists!')
            ->assertExitCode(2); // Changed to 2 which is self::INVALID
    }
    
    /** @test */
    public function it_creates_api_module_with_api_flag()
    {
        // Skip this test if no API dependencies are installed
        if (!class_exists('Laravel\Sanctum\Sanctum') && 
            !class_exists('Laravel\Passport\Passport') && 
            !class_exists('Tymon\JWTAuth\JWTAuth') && 
            !class_exists('Laravel\Airlock\Airlock')) {
            $this->markTestSkipped('No API authentication package installed');
            return;
        }
        
        $this->artisan('mod:make', ['name' => 'TestApiModule', '--api' => true])
            ->expectsOutput('✅ Module TestApiModule generated successfully! (API)')
            ->assertExitCode(0);
            
        // Assert API structure
        $this->assertDirectoryExists(base_path('modules/TestApiModule/Http/Controllers/Api'));
        $this->assertFileExists(base_path('modules/TestApiModule/Routes/api-test-api-module.php'));
    }
}