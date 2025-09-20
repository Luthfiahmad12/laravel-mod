# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.1] - 2025-09-20

### Changed
- Simplified route file structure: using `web.php` and `api.php` instead of `web-{entity}.php` and `api-{entity}.php`
- Entity routes are now added to existing route files instead of creating new ones
- Improved route addition and removal logic with proper placeholder comments
- Updated tests and documentation to reflect new route structure
- Enhanced Livewire handling consistency across commands

### Fixed
- Fixed Livewire component generation when Livewire is not installed
- Fixed file creation errors when creating modules with Livewire
- Improved error handling for file operations

## [1.1.0] - 2024-04-15

### Added
- Initial release of Laravel Mod package
- Module generation with `mod:make` command
- Entity generation with `mod:make-entity` command
- Module deletion with `mod:delete-module` command
- Entity deletion with `mod:delete-entity` command
- Module caching with `mod:cache` command
- Web and API module support
- Livewire component generation
- Automatic Livewire component discovery and registration
- GitHub Actions workflow for automated testing
- Comprehensive test suite
- Documentation and examples

### Changed
- Improved command naming consistency
- Enhanced service provider architecture
- Optimized module loading with caching
- Simplified route and view loading
- Updated API documentation to reflect Laravel Sanctum inclusion in modern Laravel versions

### Fixed
- API dependency checking for JWT Auth
- Route naming consistency
- View path organization
- Command output formatting
- Automatic Livewire component loading
- README badge configuration
- API documentation for Laravel Sanctum installation
- Improved Tymon JWT Auth class detection

## [1.0.0] - 2025-09-19

### Added
- Initial stable release