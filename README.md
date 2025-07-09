# Admin Auth Package Manager

This package provides authentication features for the admin section of your application.

## Features

- Secure admin login/logout
- Password hashing
- Profile Update
- Middleware protection for admin routes
- Session management

## Need to update `composer.json` file

Add the following to your `composer.json` to use the package from a local path:

```json
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
   php artisan admin_auth:publish --force
   composer dump-autoload
   php artisan migrate
   ```

2. Protect your admin routes using the provided middleware:
   ```php
   Route::middleware(['admin.auth'])->group(function () {
       // Admin routes here
   });
   ```

## Configuration

Edit the `config/admin_auth.php` file to customize authentication settings.

Also, update your `config/auth.php` file to add the `admin` guard and provider:

```php
'guards' => [
    // existing guards...
    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],
],

'providers' => [
    // existing providers...
    'admins' => [
        'driver' => 'eloquent',
        'model' => admin\admin_auth\Models\Admin::class,
    ],
],

## Customization

You can customize views, routes, and permissions by editing the configuration file.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
