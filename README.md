# Admin Auth Package Manager

This package provides authentication features for the admin section of your application.

---

## Features

- Secure admin login/logout
- Password hashing
- Profile Update
- Middleware protection for admin routes
- Session management

---

## Requirements

- PHP >=8.2
- Laravel Framework >= 12.x

---

## Installation

### 1. Add Git Repository to `composer.json`

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/pavanraj92/admin-auth.git"
    }
]
```

### 2. Require the package via Composer
    ```bash
    composer require admin/admin_auth:@dev
    ```

### 3. Publish assets
    ```bash
    php artisan admin_auth:publish --force
    ```
---

2. Protect your admin routes using the provided middleware:
   ```php
   Route::middleware(['admin.auth'])->group(function () {
       // Admin auth routes here
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
---

## License

This package is open-sourced software licensed under the MIT license.

