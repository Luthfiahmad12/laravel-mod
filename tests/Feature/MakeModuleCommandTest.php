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
    public function it_can_create_a_new_module()
    {
        $this->artisan('mod:make', ['name' => 'TestModule'])
            ->expectsOutput('✅ Module TestModule generated successfully!')
            ->assertExitCode(0);
            
        // Assert module directory is created
        $this->assertTrue(is_dir(base_path('modules/TestModule')));
        
        // Assert basic structure
        $this->assertTrue(is_dir(base_path('modules/TestModule/Http/Controllers')));
        $this->assertTrue(is_dir(base_path('modules/TestModule/Http/Requests')));
        $this->assertTrue(is_dir(base_path('modules/TestModule/Models')));
        $this->assertTrue(is_dir(base_path('modules/TestModule/Services')));
        $this->assertTrue(is_dir(base_path('modules/TestModule/Providers')));
        $this->assertTrue(is_dir(base_path('modules/TestModule/Routes')));
        $this->assertTrue(is_dir(base_path('modules/TestModule/Views')));
        $this->assertTrue(is_dir(base_path('modules/TestModule/Migrations')));
    }
    
    /** @test */
    public function it_cannot_create_module_if_already_exists()
    {
        // Create module first
        $this->artisan('mod:make', ['name' => 'TestModule'])
            ->assertExitCode(0);
        
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
        $this->assertTrue(is_dir(base_path('modules/TestApiModule/Http/Controllers/Api')));
        $this->assertFileExists(base_path('modules/TestApiModule/Routes/api-test-api-module.php'));
    }
}