<?php

namespace LaravelMod\Tests\Feature;

use LaravelMod\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CacheCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        // Clear caches
        Cache::forget('laravel-mod.modules');
        Cache::forget('laravel-mod.view-namespaces');
        Cache::forget('laravel-mod.route-paths');
        
        parent::tearDown();
    }
    
    /** @test */
    public function it_can_cache_module_information()
    {
        $this->artisan('mod:cache')
            ->expectsOutput('Caching module information...')
            ->expectsOutput('Module caches generated successfully!')
            ->assertExitCode(0);
            
        // Assert caches are created
        $this->assertTrue(Cache::has('laravel-mod.modules'));
        $this->assertTrue(Cache::has('laravel-mod.view-namespaces'));
        $this->assertTrue(Cache::has('laravel-mod.route-paths'));
    }
    
    /** @test */
    public function it_can_clear_module_caches()
    {
        // Create some cache first
        Cache::forever('laravel-mod.modules', ['TestModule']);
        Cache::forever('laravel-mod.view-namespaces', ['testmodule' => '/path/to/views']);
        
        // Assert caches exist
        $this->assertTrue(Cache::has('laravel-mod.modules'));
        $this->assertTrue(Cache::has('laravel-mod.view-namespaces'));
        
        // Clear caches
        $this->artisan('mod:cache', ['--clear' => true])
            ->expectsOutput('Clearing module caches...')
            ->expectsOutput('Module caches cleared successfully!')
            ->assertExitCode(0);
            
        // Assert caches are cleared
        $this->assertFalse(Cache::has('laravel-mod.modules'));
        $this->assertFalse(Cache::has('laravel-mod.view-namespaces'));
        $this->assertFalse(Cache::has('laravel-mod.route-paths'));
    }
}