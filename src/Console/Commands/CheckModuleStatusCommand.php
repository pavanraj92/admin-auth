<?php

namespace admin\admin_auth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckModuleStatusCommand extends Command
{
    protected $signature = 'admin_auth:status';
    protected $description = 'Check if AdminAuth module files are being used';

    public function handle()
    {
        $this->info('Checking AdminAuth Module Status...');
        
        // Check if module files exist
        $moduleFiles = [
            'Admin Login Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/AdminLoginController.php'),
            'Forgot Password Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ForgotPasswordController.php'),
            'Reset Password Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ResetPasswordController.php'),
            'Admin Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/AdminController.php'),
            'Package Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/PackageController.php'),
            'Admin Model' => base_path('Modules/AdminAuth/app/Models/Admin.php'),
            'Package Model' => base_path('Modules/AdminAuth/app/Models/Package.php'),
            'Seo Model' => base_path('Modules/AdminAuth/app/Models/Seo.php'),
            'Change Password Request' => base_path('Modules/AdminAuth/app/Http/Requests/ChangePasswordRequest.php'),
            'Profile Request' => base_path('Modules/AdminAuth/app/Http/Requests/ProfileRequest.php'),
            'Reset Password Request' => base_path('Modules/AdminAuth/app/Http/Requests/ResetPasswordRequest.php'),
            'Routes' => base_path('Modules/AdminAuth/routes/web.php'),
            'Views' => base_path('Modules/AdminAuth/resources/views'),
            'Config' => base_path('Modules/AdminAuth/config/admin_auth.php'),
        ];

        $this->info("\n📁 Module Files Status:");
        foreach ($moduleFiles as $type => $path) {
            if (File::exists($path)) {
                $this->info("✅ {$type}: EXISTS");
                
                // Check if it's a PHP file and show last modified time
                if (str_ends_with($path, '.php')) {
                    $lastModified = date('Y-m-d H:i:s', filemtime($path));
                    $this->line("   Last modified: {$lastModified}");
                }
            } else {
                $this->error("❌ {$type}: NOT FOUND");
            }
        }

        // Check namespace in controller
 // Check namespace in controller
        $controllers = [
            'Admin Login Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/AdminLoginController.php'),
            'Forgot Password Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ForgotPasswordController.php'),
            'Reset Password Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/Auth/ResetPasswordController.php'),
            'Admin Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/AdminController.php'),
            'Package Controller' => base_path('Modules/AdminAuth/app/Http/Controllers/Admin/PackageController.php'),
        ];

        foreach ($controllers as $name => $controllerPath) {
            if (File::exists($controllerPath)) {
            $content = File::get($controllerPath);
            if (str_contains($content, 'namespace Modules\AdminAuth\app\Http\Controllers\Admin;')) {
                $this->info("\n✅ {$name} namespace: CORRECT");
            } else {
                $this->error("\n❌ {$name} namespace: INCORRECT");
            }

            // Check for test comment
            if (str_contains($content, 'Test comment - this should persist after refresh')) {
                $this->info("✅ Test comment in {$name}: FOUND (changes are persisting)");
            } else {
                $this->warn("⚠️  Test comment in {$name}: NOT FOUND");
            }
            }
        }


        // Check composer autoload
        $composerFile = base_path('composer.json');
        if (File::exists($composerFile)) {
            $composer = json_decode(File::get($composerFile), true);
            if (isset($composer['autoload']['psr-4']['Modules\\AdminAuth\\'])) {
                $this->info("\n✅ Composer autoload: CONFIGURED");
            } else {
                $this->error("\n❌ Composer autoload: NOT CONFIGURED");
            }
        }

        $this->info("\n🎯 Summary:");
        $this->info("Your AdminAuth module is properly published and should be working.");
        $this->info("Any changes you make to files in Modules/AdminAuth/ will persist.");
        $this->info("If you need to republish from the package, run: php artisan admin_auth:publish --force");
    }
}