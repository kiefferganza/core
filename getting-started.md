# Getting Started with Baseplate Core  
**Jump-start by creating a skeleton app**

[Baseplate Core Repository](https://gitlab.com/appetiser/baseplate/backend/baseplate-core)

Baseplate Core is the foundation for all our APIs, ensuring consistency across projects by standardizing error handling, logging, telescope setup, configurations, and folder structure.

Learn more by reading the [Baseplate CORE README](https://gitlab.com/appetiser/baseplate/backend/baseplate-core).

---

## Prerequisites

Before you begin, ensure the following are set up:

1. **GitLab Account**: Make sure your account is configured for cloning repositories.
2. **Composer**: Version 2.3 or later.
3. **PHP**: Version 8.2 with the following extensions installed:
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

---

## Installing Baseplate Core

Follow these steps to set up Baseplate Core:

1. Clone the Baseplate Core repository:  
    ```bash
    git clone git@gitlab.com:appetiser/baseplate/backend/baseplate-core.git project101
    ```

2. Navigate to the project directory and install dependencies:  
    ```bash
    cd project101
    composer install
    ```

3. Configure your environment file:  
    Copy `.env.example` to `.env` and update it with your database details:  
    ```bash
    cp .env.example .env
    vim .env
    ```

4. Initialize the database:  
    ```bash
    php artisan migrate
    ```

5. Serve your application:  
    ```bash
    php artisan serve
    ```

You’re all set for the basic setup! Continue this guide until you install the basic modules and CMS! 

---

## What is a Baseplate LEGO Module?

Baseplate LEGO Modules are modular, self-contained units of functionality designed to integrate seamlessly with Baseplate Core and other modules. They help streamline application development by providing pre-built solutions for common features such as:

- User registration and authentication
- Commenting and post creation
- Profile updates
- User reporting

This modular approach accelerates development and ensures consistency across projects.

---

### LEGO Module Commands

| Command                                   | Description                                                                                 |
|-------------------------------------------|---------------------------------------------------------------------------------------------|
| `php artisan lego:list-modules`           | Lists all available LEGO modules.                                                          |
| `php artisan lego:install <module_name>`  | Installs a specific LEGO module. Use the `-r` flag to automatically register its service provider. |
| `php artisan lego:create <module_name>`   | Creates a new custom module.                                                               |

---

## Installing Baseplate LEGO Modules

For a new project, the following Baseplate Modules are commonly installed:

| Module                                                              | Description                                                        |
|----------------------------------------------------------------------|--------------------------------------------------------------------|
| [Auth Module](https://gitlab.com/appetiser/baseplate/backend/auth-module)         | User login, registration, and admin management (edit/delete users). |
| [Admin Module](https://gitlab.com/appetiser/baseplate/backend/admin-module)       | Admin profile management (update name, email, password).           |
| [App Settings Module](https://gitlab.com/appetiser/baseplate/backend/app-settings-module) | App settings like privacy policy, terms, and versioning.           |
| [Reporting Module](https://gitlab.com/appetiser/baseplate/backend/reporting-module) | Issue reporting and tracking.                                      |
| [Media Manager Module](https://gitlab.com/appetiser/baseplate/backend/media-manager-module) | Media file management.                                             |

### Installation Commands

Install these modules with the following commands:
```bash
php artisan lego:install AuthModule
php artisan lego:install AdminModule
php artisan lego:install AppSettingsModule
php artisan lego:install ReportingModule
php artisan lego:install MediaModule
```

#### Registering Modules in `concord.php`

Add the installed modules to the config/concord.php file:

```
<?php

return [
    'modules' => [
        \Appetiser\Auth\Providers\ModuleServiceProvider::class,
        \Appetiser\Admin\Providers\ModuleServiceProvider::class,
        \Appetiser\Reporting\Providers\ModuleServiceProvider::class,
        \Appetiser\Media\Providers\ModuleServiceProvider::class,
        \Appetiser\AppSettings\Providers\ModuleServiceProvider::class,
        \Appetiser\PreSigned\Providers\ModuleServiceProvider::class,
    ],
    'register_route_models' => true,
];
```

###### Initialize Database for Modules

Run the following command to set up database tables for the installed modules:

```
php artisan migrate
```
###### Change Auth Model to Baseplate

Modify the file `config/auth.php` and change the User provider to the AuthModule's User model

```
...
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => Appetiser\Auth\Models\User::class,
    ],
]
...
```

### Configuring the Filament Admin

The baseplate lego modules included in this guide Filament Admin already installed, the Admin CMS is used so that the user can  

#### Create an Admin Account

1. Create an admin account:
```
php artisan admin:new
```

2. Access the Filament admin panel:
Navigate to http://127.0.0.1:8000/admin.


For customization details, refer to the [Baseplate CORE Read.me](https://gitlab.com/appetiser/baseplate/backend/baseplate-core)



### API Documentation


We use [Bruno](https://www.usebruno.com/), a Postman alternative, for API testing and documentation.

1. Install [Bruno](https://www.usebruno.com/),

2. Clone the Baseplate API specification repository:
https://gitlab.com/appetiser/baseplate/backend/baseplate-backend-spec 

3. Open the Repository in Bruno **Note:** *DO NOT COMMIT ANYTHING to this repo*

## Creating Custom APIs

Custom API controllers can be added to the `app/Http/Controllers` directory.

While LEGO modules can be modified or extended, ensure your structure remains tidy and coherent for maintainability.
