<?php

namespace LaravelMod\Tests\Feature;

use LaravelMod\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class DeleteModuleCommandTest extends TestCase
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
    public function it_can_delete_existing_module()
    {
        // Create module first
        $this->artisan('mod:make', ['name' => 'TestModule']);
        $this->assertDirectoryExists(base_path('modules/TestModule'));
        
        // Mock confirmation
        $this->artisan('mod:delete-module', ['name' => 'TestModule'])
            ->expectsConfirmation('Are you sure you want to delete the module TestModule? This action cannot be undone.', 'yes')
            ->expectsOutput('✅ Module TestModule deleted successfully!')
            ->assertExitCode(0);
            
        // Assert module is deleted
        $this->assertDirectoryDoesNotExist(base_path('modules/TestModule'));
    }
    
    /** @test */
    public function it_cannot_delete_non_existent_module()
    {
        $this->artisan('mod:delete-module', ['name' => 'NonExistentModule'])
            ->expectsOutput('Module NonExistentModule does not exist!')
            ->assertExitCode(2); // Changed to 2 which is self::INVALID
    }
    
    /** @test */
    public function it_cancels_deletion_when_user_says_no()
    {
        // Create module first
        $this->artisan('mod:make', ['name' => 'TestModule']);
        $this->assertDirectoryExists(base_path('modules/TestModule'));
        
        // Mock cancellation
        $this->artisan('mod:delete-module', ['name' => 'TestModule'])
            ->expectsConfirmation('Are you sure you want to delete the module TestModule? This action cannot be undone.', 'no')
            ->expectsOutput('Module deletion cancelled.')
            ->assertExitCode(0);
            
        // Assert module still exists
        $this->assertDirectoryExists(base_path('modules/TestModule'));
    }
}