<?php

namespace admin\admin_auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    public function viewpackages()
    {
        try {
            $industry = DB::table('settings')->where('slug', 'industry')->value('config_value') ?? 'ecommerce';

            // Get common packages and industry-specific packages
            $commonPackages = config('constants.common_packages', []);
            $industryPackages = config("constants.industry_packages.$industry", []);
            $allPackages = config('constants.package_display_names');

            // Add settings to common packages
            $commonPackages[] = 'admin/settings';

            // Filter packages for each section
            $commonPackageList = array_intersect_key($allPackages, array_flip($commonPackages));
            $industryPackageList = array_intersect_key($allPackages, array_flip($industryPackages));

            return view('admin::admin.packages.view', compact('commonPackageList', 'industryPackageList', 'industry'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function installUninstallPackage(Request $request, $vendor, $package)
    {
        try {
            $packagePath = base_path("vendor/{$vendor}/{$package}");

            set_time_limit(0);
            chdir(base_path());

            if (is_dir($packagePath)) {
                // If uninstalling role-permission, also uninstall admins manager
                // if ($package === 'admin_role_permissions' && $vendor === 'admin') {
                //     // Uninstall admins manager first
                //     $dependentPackage = 'admins';
                //     $dependentPath = base_path("vendor/admin/{$dependentPackage}");

                //     if (is_dir($dependentPath)) {
                //         $dependentCommand = "composer remove admin/{$dependentPackage}";
                //         ob_start();
                //         passthru($dependentCommand, $dependentExitCode);
                //         ob_end_clean();
                //     }
                // }

                if ($package === 'admin_role_permissions' && $vendor === 'admin') {
                    $this->uninstallDependentPackage('admin', 'admins');
                }

                if ($package === 'users' && $vendor === 'admin') {
                    $this->uninstallDependentPackage('admin', 'user_roles');
                }

                $command = "composer remove {$vendor}/{$package}";
                ob_start();
                passthru($command, $exitCode);
                $output = ob_get_clean();

                if ($exitCode === 0) {
                    // Remove published files
                    $this->removePublishedFiles($vendor, $package);
                    $packageKey = "{$vendor}/{$package}";
                    $displayName = config("constants.package_display_names.$packageKey", $packageKey);

                    Artisan::call('optimize:clear');
                    Artisan::call('config:clear');
                    Artisan::call('view:clear');
                    $message = "Package {$displayName} Uninstalled Successfully.";
                } else {
                    $message = "❌Composer failed. Output:\n" . $output;
                    return response()->json([
                        'status' => 'error',
                        'message' => $message
                    ], 500);
                }
            } else {
                // if ($package === 'admin_role_permissions' && $vendor === 'admin') {
                //     // Install dependent package: admins
                //     $dependentPackage = 'admins';
                //     $dependentPath = base_path("vendor/admin/{$dependentPackage}");

                //     if (!is_dir($dependentPath)) {
                //         $dependentCommand = "composer require admin/{$dependentPackage}:@dev";
                //         ob_start();
                //         passthru($dependentCommand, $dependentExitCode);
                //         ob_end_clean();

                //         if ($dependentExitCode !== 0) {
                //             return response()->json([
                //                 'status' => 'error',
                //                 'message' => "❌ Failed to install dependency package: admin/{$dependentPackage}."
                //             ], 500);
                //         }

                //         // Run migrations and seeder for 'admins'
                //         Artisan::call('optimize:clear');
                //         Artisan::call('migrate', [
                //             '--path' => "vendor/admin/{$dependentPackage}/database/migrations",
                //             '--force' => true,
                //         ]);
                //     }
                // }

                if ($vendor === 'admin' && $package === 'admin_role_permissions') {
                    $this->installDependentPackage('admin', 'admins');
                }

                if ($vendor === 'admin' && $package === 'users') {
                    $this->installDependentPackage('admin', 'user_roles');
                }

                if ($vendor === 'admin' && $package === 'products') {
                    $this->installDependentPackage('admin', ['brands', 'categories', 'tags']);
                }

                $command = "composer require {$vendor}/{$package}:@dev";
                ob_start();
                passthru($command, $exitCode);
                $output = ob_get_clean();
                if ($exitCode === 0) {
                    Artisan::call('optimize:clear');
                    Artisan::call('migrate', [
                        '--path' => "vendor/{$vendor}/{$package}/database/migrations",
                        '--force' => true,
                    ]);

                    // Run the seeder
                    $seeders = [
                        'settings' => 'Admin\Settings\Database\Seeders\SettingSeeder',
                        'users' => 'Admin\Users\Database\Seeders\SeedUserRolesSeeder',
                        'admin_role_permissions' => 'Admin\AdminRolePermissions\Database\Seeders\AdminRolePermissionDatabaseSeeder',
                        'emails' => 'Admin\Emails\Database\Seeders\MailDatabaseSeeder',
                    ];

                    foreach ($seeders as $pkg => $seederClass) {
                        if (is_dir(base_path("vendor/admin/{$pkg}"))) {
                            Artisan::call('db:seed', [
                                '--class' => $seederClass,
                                '--force' => true,
                            ]);
                        }
                    }

                    $packageKey = "{$vendor}/{$package}";
                    $displayName = config("constants.package_display_names.$packageKey", $packageKey);

                    $message = "Package {$displayName} Installed Successfully.";
                } else {
                    $message = "❌Composer failed. Output:\n" . $output;
                    return response()->json([
                        'status' => 'error',
                        'message' => $message
                    ], 500);
                }
            }

            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    protected function removePublishedFiles($vendor, $package)
    {
        $singular = Str::singular($package);
        $pascalCase = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $singular)));

        $paths = [
            config_path("constants/admin/{$singular}.php"),
            resource_path("views/admin/{$singular}"),
            app_path("Http/Controllers/Admin/{$pascalCase}Manager"),
            app_path("Models/Admin/{$pascalCase}"),
            base_path("routes/admin/{$singular}.php"),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                is_dir($path) ? \File::deleteDirectory($path) : \File::delete($path);
            }
        }

        if ($package === 'admin_role_permissions') {
            // Drop tables
            Schema::dropIfExists('role_admin');
            Schema::dropIfExists('permission_role');
            Schema::dropIfExists('permissions');
            Schema::dropIfExists('roles');

            // Remove migration records
            $migrationNames = [
                'create_roles_table',
                'create_permissions_table',
                'create_permission_role_table',
                'create_role_admin_table',
            ];

            foreach ($migrationNames as $migration) {
                \DB::table('migrations')
                    ->where('migration', 'like', '%' . $migration . '%')
                    ->delete();
            }
        } else {
            if (Schema::hasTable($package)) {
                if ($package != 'admins' && Schema::hasTable('admins')) {
                    Schema::drop($package);
                }
            }

            \DB::table('migrations')
                ->where('migration', 'like', '%create_' . $package . '_table%')
                ->delete();
        }

        if ($package === 'products') {
            // Drop tables
            Schema::dropIfExists('products');
            Schema::dropIfExists('product_images');
            Schema::dropIfExists('product_category');
            Schema::dropIfExists('product_prices');
            Schema::dropIfExists('product_inventory');
            Schema::dropIfExists('product_shipping');
            Schema::dropIfExists('product_tag');

            // Remove migration records
            $migrationNames = [
                'create_products',
                'create_product_images',
                'create_product_category',
                'create_product_prices',
                'create_product_inventory',
                'create_product_shipping',
                'create_product_tag'
            ];

            foreach ($migrationNames as $migration) {
                \DB::table('migrations')
                    ->where('migration', 'like', '%' . $migration . '%')
                    ->delete();
            }
        } else {
            if (Schema::hasTable($package)) {
                if ($package != 'admins' && Schema::hasTable('admins')) {
                    Schema::drop($package);
                }
            }

            \DB::table('migrations')
                ->where('migration', 'like', '%create_' . $package . '_table%')
                ->delete();
        }


        // If package is 'users', also drop user_roles table
        if ($package === 'users' && Schema::hasTable('user_roles')) {
            Schema::drop('user_roles');
        }

        if ($package != 'admins') {
            \DB::table('migrations')
                ->where('migration', 'like', '%create_' . $package . '_table%')
                ->delete();
        }

        // Also remove user_roles migration record if applicable
        if ($package === 'users') {
            \DB::table('migrations')
                ->where('migration', 'like', '%create_user_roles_table%')
                ->delete();
        }
    }

    private function installDependentPackage($vendor, $package)
    {
        $path = base_path("vendor/{$vendor}/{$package}");
        if (!is_dir($path)) {
            $command = "composer require {$vendor}/{$package}:@dev";
            ob_start();
            passthru($command, $exitCode);
            ob_end_clean();

            if ($exitCode !== 0) {
                throw new \Exception("❌ Failed to install dependency package: {$vendor}/{$package}");
            }

            Artisan::call('optimize:clear');
            Artisan::call('migrate', [
                '--path' => "vendor/{$vendor}/{$package}/database/migrations",
                '--force' => true,
            ]);
        }
    }

    private function uninstallDependentPackage($vendor, $package)
    {
        $path = base_path("vendor/{$vendor}/{$package}");
        if (is_dir($path)) {
            $command = "composer remove {$vendor}/{$package}";
            ob_start();
            passthru($command, $exitCode);
            ob_end_clean();
        }
    }
}
