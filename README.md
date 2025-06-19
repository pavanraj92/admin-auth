# Admin Auth Package

This package provides authentication features for the admin section of your application.

## Features

- Secure admin login/logout
- Password hashing
- Middleware protection for admin routes
- Session management

## Need to update `composer.json` file

Add the following to your `composer.json` to use the package from a local path:

````json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/pavanraj92/admin-auth.git"
    }
]
```

## Installation

```bash
composer require admin/admin_auth
````

## Usage

1. Publish the config and migration files:
   ```bash
   php artisan vendor:publish --provider="admin\admin_auth\AdminModuleServiceProvider"
   ```
2. Run migrations:
   ```bash
   php artisan migrate
   ```
3. Protect your admin routes using the provided middleware:
   ```php
   Route::middleware(['admin.auth'])->group(function () {
       // Admin routes here
   });
   ```

## Configuration

Edit the `config/admin_auth.php` file to customize authentication settings.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
