<?php

namespace admin\admin_auth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AdminModuleServiceProvider extends ServiceProvider
{
    public function boot()
    {
      // Load routes, views, migrations from the package  
        $this->loadViewsFrom([
            base_path('Modules/AdminAuth/resources/views'), // Published module views first
            resource_path('views/admin/admin_auth'), // Published views second
            __DIR__ . '/../resources/views'      // Package views as fallback
        ], 'admin');
        
        // Also register module views with a specific namespace for explicit usage
        if (is_dir(base_path('Modules/AdminAuth/resources/views'))) {
            $this->loadViewsFrom(base_path('Modules/AdminAuth/resources/views'), 'admin-auth-module');
        }
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Also load migrations from published module if they exist
        if (is_dir(base_path('Modules/AdminAuth/database/migrations'))) {
            $this->loadMigrationsFrom(base_path('Modules/AdminAuth/database/migrations'));
        }
        
        // Only publish automatically during package installation, not on every request
        // Use 'php artisan auths:publish' command for manual publishing
        // $this->publishWithNamespaceTransformation();
        
        // Standard publishing for non-PHP files
        $this->publishes([
            __DIR__ . '/../database/migrations' => base_path('Modules/AdminAuth/database/migrations'),
            __DIR__ . '/../resources/views' => base_path('Modules/AdminAuth/resources/views/'),
        ], 'admin_auth');
       
        $this->registerAdminRoutes();
    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migration
        }

        $admin = DB::table('admins')
            ->orderBy('created_at', 'asc')
            ->first();

        $slug = $admin->website_slug ?? 'admin';

        $routeFile = base_path('Modules/AdminAuth/routes/web.php');
        if (!file_exists($routeFile)) {
            $routeFile = __DIR__ . '/routes/web.php'; // fallback to package route
        }

        Route::middleware('web')
            ->prefix("{$slug}/admin") // dynamic prefix
            ->group($routeFile);
    }

    public function register()
    {
        // Register the publish command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\admin_auth\Console\Commands\PublishAdminAuthModuleCommand::class,
                \admin\admin_auth\Console\Commands\CheckModuleStatusCommand::class,
                \admin\admin_auth\Console\Commands\DebugAdminAuthModuleCommand::class,
                \admin\admin_auth\Console\Commands\TestAdminAuthViewResolutionCommand::class,
            ]);
        }
    }

     /**
     * Publish files with namespace transformation
     */
    protected function publishWithNamespaceTransformation()
    {
        // Define the files that need namespace transformation
        $filesWithNamespaces = [
            // Controllers
            __DIR__ . '/../src/Controllers/Auth/AdminLoginController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/AdminLoginController.php'),
            __DIR__ . '/../src/Controllers/Auth/ForgotPasswordController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ForgotPasswordController.php'),
            __DIR__ . '/../src/Controllers/Auth/ResetPasswordController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ResetPasswordController.php'),
            __DIR__ . '/../src/Controllers/AdminController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/AdminController.php'),
            __DIR__ . '/../src/Controllers/PackageController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/PackageController.php'),
            
            // Models
            __DIR__ . '/../src/Models/Admin.php' => base_path('Modules/AdminAuth/app/Models/Admin.php'),
            __DIR__ . '/../src/Models/Package.php' => base_path('Modules/AdminAuth/app/Models/Package.php'),
            __DIR__ . '/../src/Models/Seo.php' => base_path('Modules/AdminAuth/app/Models/Seo.php'),
            
            // Requests
            __DIR__ . '/../src/Requests/ChangePasswordRequest.php' => base_path('Modules/AdminAuth/app/Http/Requests/ChangePasswordRequest.php'),
            __DIR__ . '/../src/Requests/ProfileRequest.php' => base_path('Modules/AdminAuth/app/Http/Requests/ProfileRequest.php'),
            __DIR__ . '/../src/Requests/ResetPasswordRequest.php' => base_path('Modules/AdminAuth/app/Http/Requests/ResetPasswordRequest.php'),
            
            // Routes
            __DIR__ . '/routes/web.php' => base_path('Modules/AdminAuth/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                // Create destination directory if it doesn't exist
                File::ensureDirectoryExists(dirname($destination));
                
                // Read the source file
                $content = File::get($source);
                
                // Transform namespaces based on file type
                $content = $this->transformNamespaces($content, $source);
                
                // Write the transformed content to destination
                File::put($destination, $content);
            }
        }
    }

    /**
     * Transform namespaces in PHP files
     */
    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\admin_auth\\Controllers\\Auth;' => 'namespace Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth;',
            'namespace admin\\admin_auth\\Controllers;' => 'namespace Modules\\AdminAuth\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\admin_auth\\Models;' => 'namespace Modules\\AdminAuth\\app\\Models;',
            'namespace admin\\admin_auth\\Requests;' => 'namespace Modules\\AdminAuth\\app\\Http\\Requests;',
            
            // Use statements transformations
            'use admin\\admin_auth\\Controllers\\Auth\\' => 'use Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\',
            'use admin\\admin_auth\\Controllers\\' => 'use Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\',
            'use admin\\admin_auth\\Models\\' => 'use Modules\\AdminAuth\\app\\Models\\',
            'use admin\\admin_auth\\Requests\\' => 'use Modules\\AdminAuth\\app\\Http\\Requests\\',
            
            // Class references in routes
            'admin\\admin_auth\\Controllers\\Auth\\AdminLoginController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\AdminLoginController',
            'admin\\admin_auth\\Controllers\\Auth\\ForgotPasswordController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\ForgotPasswordController',
            'admin\\admin_auth\\Controllers\\Auth\\ResetPasswordController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\ResetPasswordController',
            'admin\\admin_auth\\Controllers\\AdminController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\AdminController',
            'admin\\admin_auth\\Controllers\\PackageController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\PackageController',
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = $this->transformControllerNamespaces($content);
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = $this->transformModelNamespaces($content);
        } elseif (str_contains($sourceFile, 'Requests')) {
            $content = $this->transformRequestNamespaces($content);
        } elseif (str_contains($sourceFile, 'routes')) {
            $content = $this->transformRouteNamespaces($content);
        }

        return $content;
    }

    /**
     * Transform controller-specific namespaces
     */
    protected function transformControllerNamespaces($content)
    {
        // Update use statements for models and requests
        $content = str_replace(
            'use admin\\admin_auth\\Models\\Admin;',
            'use Modules\\AdminAuth\\app\\Models\\Admin;',
            $content
        );
        $content = str_replace(
            'use admin\\admin_auth\\Models\\Package;',
            'use Modules\\AdminAuth\\app\\Models\\Package;',
            $content
        );
        $content = str_replace(
            'use admin\\admin_auth\\Models\\Seo;',
            'use Modules\\AdminAuth\\app\\Models\\Seo;',
            $content
        );
        
        $content = str_replace(
            'use admin\\admin_auth\\Requests\\ChangePasswordRequest;',
            'use Modules\\AdminAuth\\app\\Http\\Requests\\ChangePasswordRequest;',
            $content
        );
        
        $content = str_replace(
            'use admin\\admin_auth\\Requests\\ProfileRequest;',
            'use Modules\\AdminAuth\\app\\Http\\Requests\\ProfileRequest;',
            $content
        );
        $content = str_replace(
            'use admin\\admin_auth\\Requests\\ResetPasswordRequest;',
            'use Modules\\AdminAuth\\app\\Http\\Requests\\ResetPasswordRequest;',
            $content
        );

        return $content;
    }

    /**
     * Transform model-specific namespaces
     */
    protected function transformModelNamespaces($content)
    {
        // Any model-specific transformations
        return $content;
    }

    /**
     * Transform request-specific namespaces
     */
    protected function transformRequestNamespaces($content)
    {
        // Any request-specific transformations
        return $content;
    }

    /**
     * Transform route-specific namespaces
     */
    protected function transformRouteNamespaces($content)
    {
        // Update controller references in routes
        $content = str_replace(
            'admin\\admin_auth\\Controllers\\Auth\\AdminLoginController',
            'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\AdminLoginController',
            $content
        );
        $content = str_replace(
            'admin\\admin_auth\\Controllers\\Auth\\ForgotPasswordController',
            'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\ForgotPasswordController',
            $content
        );
        $content = str_replace(
            'admin\\admin_auth\\Controllers\\Auth\\ResetPasswordController',
            'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\ResetPasswordController',
            $content
        );
        $content = str_replace(
            'admin\\admin_auth\\Controllers\\AdminController',
            'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\AdminController',
            $content
        );
        $content = str_replace(
            'admin\\admin_auth\\Controllers\\PackageController',
            'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\PackageController',
            $content
        );

        return $content;
    }
}
