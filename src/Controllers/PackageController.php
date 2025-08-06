<?php

namespace admin\admin_auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use admin\admin_auth\Models\Package;

class PackageController extends Controller
{
    public function viewpackages()
    {
        try {
            $industry = DB::table('settings')->where('slug', 'industry')->value('config_value') ?? 'ecommerce';

            // Get packages from database instead of config
            $commonPackages = Package::where('package_type', 'common')
                                   ->orWhere('package_type', 'auto_install')
                                   ->get();
            
            $industryPackages = Package::where('package_type', 'industry')
                                     ->where('industry', $industry)
                                     ->get();

            return view('admin::admin.packages.view', compact('commonPackages', 'industryPackages', 'industry'));
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

                if ($package === 'products' && $vendor === 'admin') {
                    $this->uninstallDependentPackage('admin', ['brands', 'categories', 'tags', 'product_orders']);
                }

                $command = "composer remove {$vendor}/{$package}";
                ob_start();
                passthru($command, $exitCode);
                $output = ob_get_clean();

                if ($exitCode === 0) {
                    // Remove published files
                    $this->removePublishedFiles($vendor, $package);
                    
                    // Update package status in database
                    $this->updatePackageStatus($vendor, $package, false);
                    
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
                    $this->installDependentPackage('admin', ['brands', 'categories', 'tags', 'product_orders']);
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

                    // Update package status in database
                    $this->updatePackageStatus($vendor, $package, true);

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

    /**
     * Remove published files and database artifacts for a package.
     */
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
                is_dir($path) ? File::deleteDirectory($path) : File::delete($path);
            }
        }

        // Package-specific table and migration cleanup
        switch ($package) {
            case 'admin_role_permissions':
                $tables = ['role_admin', 'permission_role', 'permissions', 'roles'];
                $migrations = [
                    'create_roles_table',
                    'create_permissions_table',
                    'create_permission_role_table',
                    'create_role_admin_table',
                ];
                break;
            case 'products':
                $tables = [
                    'products', 'product_images', 'product_category',
                    'product_prices', 'product_inventory', 'product_shipping', 'product_tag'
                ];
                $migrations = [
                    'create_products',
                    'create_product_images',
                    'create_product_category',
                    'create_product_prices',
                    'create_product_inventory',
                    'create_product_shipping',
                    'create_product_tag'
                ];
                break;
            case 'users':
                $tables = [$package, 'user_roles'];
                $migrations = ['create_' . $package . '_table', 'create_user_roles_table'];
                break;
            case 'product_orders':
                $tables = ['orders', 'order_items'];
                $migrations = ['create_orders_table', 'create_order_items_table'];
                break;
            default:
                $tables = [$package];
                $migrations = ['create_' . $package . '_table'];
                break;
        }

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        foreach ($migrations as $migration) {
            DB::table('migrations')
                ->where('migration', 'like', '%' . $migration . '%')
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

            // Update package status in database
            $this->updatePackageStatus($vendor, $package, true);

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
            
            // Update package status in database
            $this->updatePackageStatus($vendor, $package, false);
        }
    }

    /**
     * Update package installation status in the packages table
     */
    private function updatePackageStatus($vendor, $package, $isInstalled = true)
    {
        try {
            $packageName = "{$vendor}/{$package}";
            $packageRecord = Package::where('package_name', $packageName)->first();

            if ($packageRecord) {
                $packageRecord->update([
                    'is_installed' => $isInstalled,
                    'installed_at' => $isInstalled ? now() : null,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the installation process
            \Log::error("Failed to update package status for {$packageName}: " . $e->getMessage());
        }
    }

    /**
     * Get all installed packages from the packages table
     */
    public function getInstalledPackages()
    {
        return Package::installed()->active()->get();
    }

    /**
     * Get all available packages (both installed and not installed)
     */
    public function getAllPackages()
    {
        return Package::active()->get();
    }

    /**
     * Get packages by type (common or industry)
     */
    public function getPackagesByType($type)
    {
        return Package::active()->where('package_type', $type)->get();
    }

    /**
     * Get packages for specific industry
     */
    public function getPackagesForIndustry($industry)
    {
        return Package::active()->forIndustry($industry)->get();
    }
}
