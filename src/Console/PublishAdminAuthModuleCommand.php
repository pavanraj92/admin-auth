<?php

namespace admin\admin_auth\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAdminAuthModuleCommand extends Command
{
    protected $signature = 'admin_auth:publish';
    protected $description = 'Publish the admin_auth module to the Modules/AdminAuth directory with proper structure and namespace transformation';

    public function handle()
    {
        $sourceBase = base_path('packages/admin/admin_auth');
        $destBase = base_path('Modules/AdminAuth');

        // Folder mappings: package => module
        $mappings = [
            'src/Controllers' => 'app/Http/Controllers/Admin',
            'src/Models' => 'app/Models',
            'src/Requests' => 'app/Http/Requests',
            'src/routes/web.php' => 'routes/web.php',
            'resources/views' => 'resources/views',
            'resources/assets' => 'resources/assets',
            'database/migrations' => 'database/migrations',
        ];

        foreach ($mappings as $src => $dest) {
            $srcPath = $sourceBase . '/' . $src;
            $destPath = $destBase . '/' . $dest;
            if (File::exists($srcPath)) {
                if (is_dir($srcPath)) {
                    File::ensureDirectoryExists($destPath);
                    File::copyDirectory($srcPath, $destPath);
                    $this->info("Published directory: $srcPath → $destPath");
                } else {
                    File::ensureDirectoryExists(dirname($destPath));
                    $content = File::get($srcPath);
                    if (str_ends_with($srcPath, '.php')) {
                        $content = $this->transformNamespaces($content, $srcPath);
                    }
                    File::put($destPath, $content);
                    $this->info("Published file: $srcPath → $destPath");
                }
            }
        }
        $this->info("admin_auth module published to $destBase");
    }

    protected function transformNamespaces($content, $sourceFile)
    {
        $namespaceTransforms = [
            // Controllers
            'namespace admin\\admin_auth\\Controllers;' => 'namespace Modules\\AdminAuth\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\admin_auth\\Models;' => 'namespace Modules\\AdminAuth\\app\\Models;',
            'namespace admin\\admin_auth\\Requests;' => 'namespace Modules\\AdminAuth\\app\\Http\\Requests;',
            // Use statements
            'use admin\\admin_auth\\Controllers\\' => 'use Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\',
            'use admin\\admin_auth\\Models\\' => 'use Modules\\AdminAuth\\app\\Models\\',
            'use admin\\admin_auth\\Requests\\' => 'use Modules\\AdminAuth\\app\\Http\\Requests\\',
        ];
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        return $content;
    }
} 