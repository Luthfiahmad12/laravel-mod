<?php

namespace LaravelMod\Tests\Unit;

use LaravelMod\Tests\TestCase;
use LaravelMod\Providers\ModServiceProvider;

class ModServiceProviderTest extends TestCase
{
    /** @test */
    public function it_has_the_correct_provider_class()
    {
        $provider = new ModServiceProvider($this->app);
        $this->assertInstanceOf(ModServiceProvider::class, $provider);
    }
    
    /** @test */
    public function it_can_get_active_modules_method()
    {
        $provider = new ModServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'getActiveModules'));
    }
    
    /** @test */
    public function it_can_load_module_routes_method()
    {
        $provider = new ModServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'loadModuleRoutes'));
    }
    
    /** @test */
    public function it_can_load_module_views_method()
    {
        $provider = new ModServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'loadModuleViews'));
    }
    
    /** @test */
    public function it_can_load_module_migrations_method()
    {
        $provider = new ModServiceProvider($this->app);
        $this->assertTrue(method_exists($provider, 'loadModuleMigrations'));
    }
}