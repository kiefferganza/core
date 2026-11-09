# Appetiser Baseplate Core

Baseplate Core is a boilerplate Laravel backend API framework with modular architecture powered by [Concord Modules](https://konekt.dev/concord/1.x/README). It includes a built-in `lego` command to manage installable modules, providing a flexible and scalable structure for backend development.

## Table of Contents

- [Features](#features)
- [Modules](#modules)
- [Requirements](#requirements)
- [Installation](#installation-5-minutes)
- [Lego Commands](#lego-commands)
- [Filament Admin Panel](#filament-admin-panel)
- [S3 CORS Configuration](#s3-cors-configuration-optional)
- [Coding Standards](#coding-standards)
- [CI/CD](#cicd)
- [Contributing](#contributing)

## Features

- Laravel 
- Modular structure using Concord
- Preconfigured with:
  - Concord Modules
  - Composer Merge Plugin
  - `lego` CLI commands for module management
- Integrated [Filament Admin](https://filamentphp.com/) CMS support
- **Pest** - Testing framework
  - Read more at [Pest Documentation](https://pestphp.com/)
- **Laravel Boost** - MCP server for AI-assisted development with documentation search, Artisan commands, Tinker sessions, and debugging tools
  - Read more at [Laravel Boost Documentation](http://boost.laravel.com/)

## Modules

Modules are Concord in-app packages, easily installed via the `lego` command:

```
php artisan lego:install ModuleName
```

Recommended read: [Concord Modules Overview](https://konekt.dev/concord/1.x/modules)

## Requirements

- PHP: ^8.3 or higher
- Composer: v2.3 or later
- Extensions:
  - BCMath
  - Ctype
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - SQLite 3
  - Tokenizer
  - XML
  - Exif

Note: Use an older version of Baseplate Core if your PHP version is lower than 8.2.

## Installation via script - Mac/Linux Only
Note: Make sure you installed all php extension

```
git clone git@gitlab.com:appetiser/baseplate/backend/baseplate-core.git project101
cd project101
./setup.sh
```


## Normal Installation 

### 1. Clone the Baseplate core Repository

```
git clone git@gitlab.com:appetiser/baseplate/backend/baseplate-core.git project101
cd project101
composer setup
```

### 2. Install Core Modules

```
php artisan lego:install AuthModule, SMSModule
```
or you can opt to leave as blank and select your desired modules interactively.

```
// php artisan lego:install

┌ Select modules to install (use Space to select, Enter to confirm): ────────────────┐
│ › ◻ AWSToolsModule - Allows interaction with AWS                                 ┃ │
│   ◻ AdminModule - Provides admin users and admin authentication.                 │ │
│   ◻ AppSettingsModule - Provides App Versioning and Page Settings                │ │
│   ◻ AuthModule - Provides authentication                                         │ │
│   ◻ BlockingModule - Provides blocking usage.                                    │ │
└─────────────────────────────────────────────────────────────────────── 0 selected  ┘
You can select multiple modules. Already installed modules are marked.
```

Install module dependencies through composer

```
composer install
```

### 3. Register Modules in `config/concord.php`

```php
return [
    'modules' => [
        \Appetiser\Auth\Providers\ModuleServiceProvider::class,
        \Appetiser\Admin\Providers\ModuleServiceProvider::class,
        \Appetiser\Reporting\Providers\ModuleServiceProvider::class,
        \Appetiser\Media\Providers\ModuleServiceProvider::class,
        \Appetiser\AppSettings\Providers\ModuleServiceProvider::class,
    ],
    'register_route_models' => true,
];
```

### 4. Serve the App

```
php artisan serve
```
or any other preferred method.

Your project is now ready to use

1. Admin Filament URL: `{{product local domain}}/admin`

2. Telescope API: `{{product local domain}}/telescope`

3. API URL: `{{product local domain}}/api/v1`

## Lego Commands

- `php artisan lego:list-modules` – View all installable modules.
- `php artisan lego:install {module_name}` – Install one or more modules.
  - Use the `-r` or `--register` flag to automatically register the module's service provider in `config/concord.php`.
  - Use the `--dev` flag to keep the `.git` folder for development purposes (allows you to track and commit changes within the module).
- `php artisan lego:create {module_name}` – Create a new module.

## Filament Admin Panel

Prerequisites:
- Install `AdminModule` via lego command:

```
php artisan lego:install AdminModule
```

Baseplate Core includes support for [Filament PHP](https://filamentphp.com/) for building admin panels.

### 1. Create an Admin User

```
php artisan admin:new
```

### 2. Access the Admin Panel

Visit: `{{project local domain}}/admin`



Note: you can initialize a new Filament Panel Provider by 

```
php artisan initiate-cms
```

This generates `Providers/Filament/AdminPanelProvider.php`.

Modules that contain Filament resources, pages, widgets, or Livewire components should be registered inside `AdminPanelProvider.php`.

### Example: Dynamic Panel Configuration

```php
return $panel
    ->when($isLocal, function (Panel $panel) {
        $panel->domain(config('app.url'));
        $panel->path('admin');
    }, function (Panel $panel) {
        $panel->domain(config('app.cms_url'));
    });
```

### Auto-Discover Filament Resources from Registered Modules

```php
class SampleAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ...
            ->autoDiscoverModuleResources()
            ...;
    }
}
```

### Running on a Subdomain

Update `.env`:

```
APP_URL=http://localhost
APP_CMS_URL=http://cms.localhost
```

Update the panel config:

```php
return $panel->domain(config('app.cms_url'));
```

## S3 CORS Configuration (Optional)

```json
[
    {
        "AllowedHeaders": ["*"],
        "AllowedMethods": ["HEAD", "GET", "PUT", "POST", "DELETE"],
        "AllowedOrigins": ["*"],
        "ExposeHeaders": ["Access-Control-Allow-Origin"],
        "MaxAgeSeconds": 3000
    }
]
```

## Coding Standards

PSR-2 is enforced using `phpcs.xml`.

Check your code:

```
vendor/bin/phpcs
```

## CI/CD

CI/CD integration is under development. Stay tuned for updates.

## Contributing

If you find any issues, typos, or bugs, feel free to submit a merge request:  
https://docs.gitlab.com/ee/gitlab-basics/add-merge-request.html

Maintainers:

- Anthony Alaan (anthony.alaan@appetiser.com.au)
- Wilmon Agulo (wilmon.agulo@appetiser.com.au)
