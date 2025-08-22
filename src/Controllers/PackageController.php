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
            $isPackageInstalled = Package::where(['vendor' => $vendor, 'name' => $package, 'is_installed' => 1])->exists();

            set_time_limit(0);
            chdir(base_path());

            if (!empty($isPackageInstalled)) {

                if ($package === 'admin_role_permissions' && $vendor === 'admin') {
                    $this->uninstallDependentPackage('admin', 'admins');
                }

                if ($package === 'users' && $vendor === 'admin') {
                    $this->uninstallDependentPackage('admin', 'user_roles');
                }

                if ($package === 'products' && $vendor === 'admin') {
                    $this->uninstallDependentPackage('admin', ['wishlists', 'ratings', 'tags']);
                }

                if ($package === 'courses' && $vendor === 'admin') {
                    $this->uninstallDependentPackage('admin', ['quizzes', 'ratings', 'tags', 'wishlists']);
                }

                // if ($package === 'coupons' && $vendor === 'admin') {
                //     $this->uninstallDependentPackage('admin', ['courses']);
                // }

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

                $dependencyMap = [
                    'admin/admin_role_permissions' => ['admins'],
                    'admin/products' => ['users',  'user_roles', 'brands', 'categories', 'tags'],
                    'admin/courses' => ['users',  'user_roles', 'categories', 'tags'],
                    'admin/users' => ['user_roles'],
                    'admin/quizzes' => ['users', 'user_roles', 'categories', 'tags', 'courses'],
                    'admin/coupons' => [
                        'ecommerce' => ['users', 'user_roles', 'categories', 'tags', 'brands', 'products'],
                        'education' => ['users', 'user_roles', 'categories', 'tags', 'courses'],
                    ],
                    'admin/wishlists' => [
                        'ecommerce' => ['users', 'user_roles', 'categories', 'tags', 'brands', 'products'],
                        'education' => ['users', 'user_roles', 'categories', 'tags', 'courses'],
                    ],
                    'admin/ratings' => [
                        'ecommerce' => ['users', 'user_roles', 'categories', 'tags', 'brands', 'products'],
                        'education' => ['users', 'user_roles', 'categories', 'tags', 'courses'],
                    ],
                ];
                $packageKey = "{$vendor}/{$package}";

                // Handle coupon package dependencies based on industry
                $industry = config('GET.industry'); // or however you detect industry

                if (isset($dependencyMap[$packageKey])) {
                    $deps = $dependencyMap[$packageKey];
                    if (is_array($deps) && isset($deps[$industry])) {
                        $deps = $deps[$industry];
                    }
                    $this->installDependentPackage($vendor, $deps);
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
                        'shipping_charges' => 'Admin\ShippingCharges\Database\Seeders\ShippingZoneSeeder',
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
                    'product_images',
                    'product_categories',
                    'product_prices',
                    'product_inventories',
                    'product_shippings',
                    'product_tags',
                    'order_addresses',
                    'return_refund_requests',
                    'transactions',
                    'products',
                    'order_items',
                    'orders',
                    'ratings',
                    'wishlists'
                ];
                $migrations = [
                    'create_product_images',
                    'create_product_categories',
                    'create_product_prices',
                    'create_product_inventories',
                    'create_product_shippings',
                    'create_product_tags',
                    'create_order_items_table',
                    'create_shipping_addresses',
                    'create_return_refund_requests',
                    'transactions',
                    'create_products',
                    'create_orders_table',
                    'create_ratings_table',
                    'create_wishlists_table'
                ];
                break;
            case 'users':
                $tables = [$package, 'user_roles'];
                $migrations = ['create_' . $package . '_table', 'create_user_roles_table'];
                break;
            case 'courses':
                $tables = ['course_category', 'course_purchases', 'course_sections', 'course_tag', 'transactions', 'lectures', 'courses', 'quiz_answers', 'quiz_questions', 'quizzes', 'ratings', 'wishlists'];
                $migrations = [
                    'create_courses_table',
                    'create_course_category_table',
                    'create_course_tag_table',
                    'create_course_sections_table',
                    'create_lectures_table',
                    'create_course_purchases_table',
                    'create_transactions_table',
                    'create_quiz_answers_table',
                    'create_quiz_questions_table',
                    'create_quizzes_table',
                    'create_ratings_table',
                    'create_wishlists_table'
                ];
                break;
            case 'coupons':
                $tables = ['coupon_category', 'coupon_course', 'coupon_product', 'coupons'];
                $migrations = [
                    'create_coupon_category_table',
                    'create_coupon_course_table',
                    'create_coupon_product_table',
                    'create_coupons_table',
                ];
                break;
            case 'quizzes':
                $tables = ['quiz_answers', 'quiz_questions', 'quizzes'];
                $migrations = [
                    'create_quiz_answers_table',
                    'create_quiz_questions_table',
                    'create_quizzes_table',
                ];
                break;
            case 'quizzes':
                $tables = ['quiz_answers', 'quiz_questions', 'quizzes'];
                $migrations = [
                    'create_quiz_answers_table',
                    'create_quiz_questions_table',
                    'create_quizzes_table',
                ];
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

    private function installDependentPackage($vendor, $packages)
    {
        $packages = (array) $packages; // cast to array always

        foreach ($packages as $package) {
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

                $this->updatePackageStatus($vendor, $package, true);

                // 🔁 Check if this package has its own dependencies
                if (isset($this->dependencies[$package])) {
                    $this->installDependentPackage($vendor, $this->dependencies[$package]);
                }
            }
        }
    }

    private function uninstallDependentPackage($vendor, $packages)
    {
        $packages = (array) $packages; // always convert to array

        foreach ($packages as $package) {
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
