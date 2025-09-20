# Laravel Mod

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-mod/core.svg?style=flat-square)](https://packagist.org/packages/laravel-mod/core)
[![Tests](https://github.com/Luthfiahmad12/laravel-mod/actions/workflows/tests.yml/badge.svg)](https://github.com/Luthfiahmad12/laravel-mod/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-mod/core.svg?style=flat-square)](https://packagist.org/packages/laravel-mod/core)

A **lite modular package** for Laravel that helps you organize your application into clean, maintainable modules without the complexity of large modular systems.

Perfect for small to medium projects that still want maintainable code.

## ✨ Features

-   Generate modules with a single command
-   Generate entities within existing modules
-   Delete modules and entities
-   Cache module information for better performance
-   Auto-discovery support - no manual registration required
-   Provides common structure out of the box:
    -   Controllers
    -   Models
    -   Requests
    -   Services
    -   Providers
    -   Routes
    -   Views
    -   Livewire components (for web modules)
    -   Migrations
-   API module generation with `--api` flag
-   Auto-replaces namespace & module naming

## 🔧 Installation

```bash
composer require laravel-mod/core
```

### Requirements

-   **PHP**: 8.1 or higher
-   **Laravel**: 11 or higher

> Note: This package uses Laravel's auto-discovery feature and will be automatically registered after installation.

## 🚀 Quick Start

### Generate a new web module

```bash
php artisan mod:make Blog
```

### Generate a new API module

First, install an API authentication package:

```bash
# For Laravel Sanctum (recommended)
php artisan install:api

# OR for Laravel Passport
php artisan install:api --passport
```

Then generate the API module:

```bash
php artisan mod:make Blog --api
```

### Generate a new entity within an existing module

For web modules:

```bash
php artisan mod:make-entity Blog Post
```

For API modules:

```bash
php artisan mod:make-entity Blog Post --api
```

### Delete a module

```bash
php artisan mod:delete-module Blog
```

### Delete an entity from a module

```bash
php artisan mod:delete-entity Blog Post
```

### Manage module caches

```bash
# Cache module information for better performance
php artisan mod:cache

# Clear module caches
php artisan mod:cache --clear
```

## 🧪 Testing

```bash
composer test
```

Or with coverage:

```bash
composer test-coverage
```

## 📁 Module Structure

### Web Module

```
modules/
└── ModuleName/
    ├── Http/
    │   ├── Controllers/
    │   └── Requests/
    ├── Models/
    ├── Services/
    ├── Providers/
    │   └── ModuleNameServiceProvider.php
    ├── Routes/
    │   └── web-*.php
    ├── Views/
    ├── Migrations/
    └── Livewire/ (if installed)
```

### API Module

```
modules/
└── ModuleName/
    ├── Http/
    │   ├── Controllers/
    │   │   └── Api/
    │   └── Requests/
    ├── Models/
    ├── Services/
    ├── Providers/
    │   └── ModuleNameServiceProvider.php
    ├── Routes/
    │   └── api-*.php
    ├── Views/
    └── Migrations/
```

## 🛠 Module Components

### Generated Components

When you create a module, the following components are automatically generated:

1. **Controller** - With a single `index()` method that returns a view
2. **Model** - With `$table` property pre-configured
3. **Request** - Empty form request for validation
4. **Service** - Empty service class with placeholder comment
5. **Service Provider** - For registering module-specific services
6. **Routes** - Single route file with `index` route only
7. **Views** - Single `index.blade.php` view file
8. **Migrations** - Standard Laravel migration file

### Route Naming

All routes follow a consistent naming pattern:

-   Web routes: `web-entity.php` (e.g., `web-post.php`)
-   API routes: `api-entity.php` (e.g., `api-post.php`)
-   Route names: `entity.index` (e.g., `post.index`)

## 🛠 API Module Features

When generating an API module with the `--api` flag, you get:

-   **Minimal API Controller** with only `index()` method
-   **API Routes** with proper middleware applied
-   **API Controllers** located in `Http/Controllers/Api/` namespace
-   **No web-specific components** (Livewire components)
-   **Optimized structure** for API-focused development

The generated API controller includes only the standard `index()` method:

-   `index()` - Get a collection of resources

Route files are named dynamically (`api-{entity}.php`) for better organization.

## 🛠 Module Service Provider

Each module includes a service provider (`ModuleNameServiceProvider`) that can be used to register module-specific services, bindings, and bootstrapping logic.

**Note:** Routes, views, and migrations are automatically loaded by the main LaravelMod package service provider. The module service provider should only be used for:

-   Registering service bindings and singletons
-   Registering blade directives
-   Registering middleware
-   Other module-specific bootstrapping logic

Example usage in `Providers/ModuleNameServiceProvider.php`:

```php
public function register(): void
{
    // Register module services
    $this->app->bind(
        \App\Modules\Blog\Services\PostServiceInterface::class,
        \App\Modules\Blog\Services\PostService::class
    );
}

public function boot(): void
{
    // Register blade directives
    Blade::directive('blog', function ($expression) {
        return "<?php echo 'Blog Module: ' . {$expression}; ?>";
    });
}
```

## 📝 Notes

-   API modules require either Laravel Sanctum or Passport for authentication
-   Laravel Sanctum is recommended for most API applications
-   All modules are automatically registered with Laravel's service container
-   Entity generation automatically detects module type (web or API)
-   Livewire components are automatically discovered and registered if Livewire is installed
-   Route files are named dynamically for better organization
-   Middleware is applied automatically by the service provider
-   Package follows "less is more" philosophy - extend as needed

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
