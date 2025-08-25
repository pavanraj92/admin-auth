<?php

namespace admin\admin_auth\Console\Commands;

use Illuminate\Console\Command;

class DebugAdminAuthModuleCommand extends Command
{
    protected $signature = 'admin_auth:debug';
    protected $description = 'Debug Admin Auth module loading';

    public function handle()
    {
       $this->info('🔍 Debugging Admin Auth Module...');
        
        // Check which route file is being loaded
        $this->info("\n📍 Route Loading Priority:");
        $moduleRoutes = base_path('Modules/AdminAuth/routes/web.php');
        $packageRoutes = base_path('packages/admin/admin_auth/src/routes/web.php');
        
        if (File::exists($moduleRoutes)) {
            $this->info("✅ Module routes found: {$moduleRoutes}");
            $this->info("   Last modified: " . date('Y-m-d H:i:s', filemtime($moduleRoutes)));
        } else {
            $this->error("❌ Module routes not found");
        }
        
        if (File::exists($packageRoutes)) {
            $this->info("✅ Package routes found: {$packageRoutes}");
            $this->info("   Last modified: " . date('Y-m-d H:i:s', filemtime($packageRoutes)));
        } else {
            $this->error("❌ Package routes not found");
        }
        
        // Check view loading priority
        $this->info("\n👀 View Loading Priority:");
        $viewPaths = [
            'Module views' => base_path('Modules/AdminAuth/resources/views'),
            'Published views' => resource_path('views/admin/admin_auth'),
            'Package views' => base_path('packages/admin/admin_auth/src/resources/views'),
        ];
        
        foreach ($viewPaths as $name => $path) {
            if (File::exists($path)) {
                $this->info("✅ {$name}: {$path}");
            } else {
                $this->warn("⚠️  {$name}: NOT FOUND - {$path}");
            }
        }
        
        // Check controller resolution
        $this->info("\n🎯 Controller Resolution:");

        $controllers = [
            'AdminLoginController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\AdminLoginController',
            'ForgotPasswordController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\ForgotPasswordController',
            'ResetPasswordController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\Auth\\ResetPasswordController',
            'AdminController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\AdminController',
            'PackageController' => 'Modules\\AdminAuth\\app\\Http\\Controllers\\Admin\\PackageController',
        ];

        foreach ($controllers as $label => $controllerClass) {
            $this->info("Checking {$label}: {$controllerClass}");
            if (class_exists($controllerClass)) {
            $this->info("✅ Controller class found: {$controllerClass}");
            $reflection = new \ReflectionClass($controllerClass);
            $this->info("   File: " . $reflection->getFileName());
            $this->info("   Last modified: " . date('Y-m-d H:i:s', filemtime($reflection->getFileName())));
            } else {
            $this->error("❌ Controller class not found: {$controllerClass}");
            }
        }
        
        // Show current routes
        $this->info("\n🛣️  Current Routes:");
        $routes = Route::getRoutes();
        $shippingRoutes = [];

        foreach ($routes as $route) {
            $action = $route->getAction();
            if (isset($action['controller'])) {
            if (
                str_contains($action['controller'], 'AdminLoginController') ||
                str_contains($action['controller'], 'ForgotPasswordController') ||
                str_contains($action['controller'], 'ResetPasswordController') ||
                str_contains($action['controller'], 'AdminController') ||
                str_contains($action['controller'], 'PackageController')
            ) {
                $shippingRoutes[] = [
                'uri' => $route->uri(),
                'methods' => implode('|', $route->methods()),
                'controller' => $action['controller'],
                'name' => $route->getName(),
                ];
            }
            }
        }
        
        if (!empty($shippingRoutes)) {
            $this->table(['URI', 'Methods', 'Controller', 'Name'], $shippingRoutes);
        } else {
            $this->warn("No shipping routes found.");
        }
    
    }
} 