<?php

namespace admin\admin_auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function viewpackages()
    {
        try {
            $packages = config('constants.package_display_names');
            return view('admin::admin.packages.view', compact('packages'));
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
                if ($package === 'admin_role_permissions' && $vendor === 'admin') {
                    // Uninstall admins manager first
                    $dependentPackage = 'admins';
                    $dependentPath = base_path("vendor/admin/{$dependentPackage}");

                    if (is_dir($dependentPath)) {
                        $dependentCommand = "composer remove admin/{$dependentPackage}";
                        ob_start();
                        passthru($dependentCommand, $dependentExitCode);
                        ob_end_clean();
                    }
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
                if ($package === 'admin_role_permissions' && $vendor === 'admin') {
                    // Install dependent package: admins
                    $dependentPackage = 'admins';
                    $dependentPath = base_path("vendor/admin/{$dependentPackage}");

                    if (!is_dir($dependentPath)) {
                        $dependentCommand = "composer require admin/{$dependentPackage}:@dev";
                        ob_start();
                        passthru($dependentCommand, $dependentExitCode);
                        ob_end_clean();

                        if ($dependentExitCode !== 0) {
                            return response()->json([
                                'status' => 'error',
                                'message' => "❌ Failed to install dependency package: admin/{$dependentPackage}."
                            ], 500);
                        }

                        // Run migrations and seeder for 'admins'
                        Artisan::call('optimize:clear');
                        Artisan::call('migrate', [
                            '--path' => "vendor/admin/{$dependentPackage}/database/migrations",
                            '--force' => true,
                        ]);
                    }
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
                    if (is_dir(base_path('vendor/admin/settings'))) {
                        Artisan::call('db:seed', [
                            '--class' => 'Admin\Settings\Database\Seeders\\SettingSeeder',
                            '--force' => true,
                        ]);
                    }

                    if (is_dir(base_path('vendor/admin/users'))) {
                        Artisan::call('db:seed', [
                            '--class' => 'Admin\Users\Database\Seeders\\SeedUserRolesSeeder',
                            '--force' => true,
                        ]);
                    }

                    if (is_dir(base_path('vendor/admin/admin_role_permissions'))) {
                        Artisan::call('db:seed', [
                            '--class' => 'Admin\AdminRolePermissions\Database\Seeders\\AdminRolePermissionDatabaseSeeder',
                            '--force' => true,
                        ]);
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
}
