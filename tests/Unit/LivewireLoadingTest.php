<?php

namespace LaravelMod\Tests\Unit;

use LaravelMod\Tests\TestCase;
use LaravelMod\Providers\ModServiceProvider;
use Illuminate\Support\Facades\File;

class LivewireLoadingTest extends TestCase
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
    public function it_can_load_module_livewire_components_method()
    {
        $provider = new ModServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'loadModuleLivewireComponents'));
    }
    
    /** @test */
    public function it_has_load_module_livewire_components_method()
    {
        // This test ensures that the method exists and is accessible through reflection
        $provider = new ModServiceProvider($this->app);
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('loadModuleLivewireComponents');
        
        $this->assertTrue($method->isProtected());
    }
}