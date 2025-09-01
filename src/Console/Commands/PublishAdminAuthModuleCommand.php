<?php

namespace admin\admin_auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAdminAuthModuleCommand extends Command
{
   protected $signature = 'admin_auth:publish {--force : Force overwrite existing files}';
    protected $description = 'Publish Admin Auth module files with proper namespace transformation';

    public function handle()
    {
        $this->info('Publishing Admin Auth module files...');

        // Check if module directory exists
        $moduleDir = base_path('Modules/AdminAuth');
        if (!File::exists($moduleDir)) {
            File::makeDirectory($moduleDir, 0755, true);
        }

        // Publish with namespace transformation
        $this->publishWithNamespaceTransformation();
        
        // Publish other files
        $this->call('vendor:publish', [
            '--tag' => 'admin_auth',
            '--force' => $this->option('force')
        ]);

        // Update composer autoload
        $this->updateComposerAutoload();

        $this->info('Admin Auth module published successfully!');
        $this->info('Please run: composer dump-autoload');
    }

    protected function publishWithNamespaceTransformation()
    {
        $basePath = dirname(dirname(__DIR__)); // Go up to packages/admin/admin_auth/src

        $filesWithNamespaces = [
            // Controllers
            $basePath . '/Controllers/Auth/AdminLoginController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/AdminLoginController.php'),
            $basePath . '/Controllers/Auth/ForgotPasswordController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ForgotPasswordController.php'),
            $basePath . '/Controllers/Auth/ResetPasswordController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ResetPasswordController.php'),
            $basePath . '/Controllers/AdminController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/AdminController.php'),
            $basePath . '/Controllers/PackageController.php' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/PackageController.php'),

            // Models
            $basePath . '/Models/Admin.php' => base_path('Modules/AdminAuth/app/Models/Admin.php'),
            $basePath . '/Models/Package.php' => base_path('Modules/AdminAuth/app/Models/Package.php'),
            $basePath . '/Models/Seo.php' => base_path('Modules/AdminAuth/app/Models/Seo.php'),

            // Requests
            $basePath . '/Requests/ChangePasswordRequest.php' => base_path('Modules/AdminAuth/app/Http/Requests/ChangePasswordRequest.php'),
            $basePath . '/Requests/ProfileRequest.php' => base_path('Modules/AdminAuth/app/Http/Requests/ProfileRequest.php'),
            $basePath . '/Requests/ResetPasswordRequest.php' => base_path('Modules/AdminAuth/app/Http/Requests/ResetPasswordRequest.php'),

            // Routes
            $basePath . '/routes/web.php' => base_path('Modules/AdminAuth/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                File::ensureDirectoryExists(dirname($destination));
                
                $content = File::get($source);
                $content = $this->transformNamespaces($content, $source);
                
                File::put($destination, $content);
                $this->info("Published: " . basename($destination));
            } else {
                $this->warn("Source file not found: " . $source);
            }
        }
    }

    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\admin_auth\\Controllers;' => 'namespace Modules\\AdminAuth\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\admin_auth\\Models;' => 'namespace Modules\\AdminAuth\\app\\Models;',
            'namespace admin\\admin_auth\\Requests;' => 'namespace Modules\\AdminAuth\\app\\Http\\Requests;',

            // Use statements transformations
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
            $content = str_replace('use admin\\admin_auth\\Models\\Admin;', 'use Modules\\AdminAuth\\app\\Models\\Admin;', $content);
            $content = str_replace('use admin\\admin_auth\\Models\\Package;', 'use Modules\\AdminAuth\\app\\Models\\Package;', $content);
            $content = str_replace('use admin\\admin_auth\\Models\\Seo;', 'use Modules\\AdminAuth\\app\\Models\\Seo;', $content);
            $content = str_replace('use admin\\admin_auth\\Requests\\ChangePasswordRequest;', 'use Modules\\AdminAuth\\app\\Http\\Requests\\ChangePasswordRequest;', $content);
            $content = str_replace('use admin\\admin_auth\\Requests\\ProfileRequest;', 'use Modules\\AdminAuth\\app\\Http\\Requests\\ProfileRequest;', $content);
            $content = str_replace('use admin\\admin_auth\\Requests\\ResetPasswordRequest;', 'use Modules\\AdminAuth\\app\\Http\\Requests\\ResetPasswordRequest;', $content);
        }

        return $content;
    }

    protected function updateComposerAutoload()
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        // Add module namespace to autoload
        if (!isset($composer['autoload']['psr-4']['Modules\\AdminAuth\\'])) {
            $composer['autoload']['psr-4']['Modules\\AdminAuth\\'] = 'Modules/AdminAuth/app/';

            File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated composer.json autoload');
        }
    }
} 