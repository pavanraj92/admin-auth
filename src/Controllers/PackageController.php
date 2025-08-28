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
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    /**
     * Central dependency map for all packages
     */
    protected array $dependencyMapForInstall = [
        'admin/admin_role_permissions' => ['admins'],
        'admin/products' => ['users',  'user_roles', 'brands', 'categories'],
        'admin/courses' => ['users',  'user_roles', 'categories'],
        'admin/users' => ['user_roles'],
        'admin/quizzes' => ['users', 'user_roles', 'categories', 'courses', 'tags'],
        'admin/product_transactions' => ['users', 'user_roles', 'categories', 'brands', 'products'],
        'admin/product_inventories' => ['users', 'user_roles', 'categories', 'brands', 'products'],
        'admin/product_reports' => ['users', 'user_roles', 'categories', 'brands', 'products'],
        'admin/product_return_refunds' => ['users', 'user_roles', 'categories', 'brands', 'products'],
        'admin/course_reports' => ['users', 'user_roles', 'categories', 'courses'],
        'admin/course_transactions' => ['users', 'user_roles', 'categories', 'courses'],
        'admin/coupons' => [
            'ecommerce' => ['users', 'user_roles', 'categories', 'brands', 'products', 'product_transactions'],
            'education' => ['users', 'user_roles', 'categories', 'courses', 'course_transactions'],
        ],
        'admin/wishlists' => [
            'ecommerce' => ['users', 'user_roles', 'categories', 'brands', 'products'],
            'education' => ['users', 'user_roles', 'categories', 'courses'],
        ],
        'admin/ratings' => [
            'ecommerce' => ['users', 'user_roles', 'categories', 'brands', 'products'],
            'education' => ['users', 'user_roles', 'categories', 'courses'],
        ],
        'admin/tags' => [
            'ecommerce' => ['users', 'user_roles', 'categories', 'brands', 'products'],
            'education' => ['users', 'user_roles', 'categories', 'courses'],
        ],
        'admin/commissions' => [
            'ecommerce' => ['users', 'user_roles', 'categories', 'brands', 'products', 'product_transactions'],
            'education' => ['users', 'user_roles', 'categories', 'courses', 'course_transactions'],
        ],
    ];  

    protected array $dependencyMapForUnInstall = [
        'admin/admin_role_permissions' => ['admins'],
        'admin/users' => ['user_roles'],
    ];    

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
            set_time_limit(0);
            chdir(base_path());

            $packageKey = "{$vendor}/{$package}";
            $industry = DB::table('settings')->where('slug', 'industry')->value('config_value') ?? 'ecommerce';

            $isPackageInstalled = Package::where([
                'vendor' => $vendor,
                'name' => $package,
                'is_installed' => 1
            ])->exists();



            // UNINSTALL
            if ($isPackageInstalled) {
                // Uninstall dependents FIRST (reverse dependency order)
                $this->uninstallDependentPackage($vendor, $packageKey, $industry);

                // Remove the package itself
                $command = "composer remove {$vendor}/{$package}";
                ob_start();
                passthru($command, $exitCode);
                $output = ob_get_clean();

                if ($exitCode === 0) {
                    $this->removePublishedFiles($vendor, $package);
                    $this->updatePackageStatus($vendor, $package, false);

                    $displayName = config("constants.package_display_names.$packageKey", $packageKey);

                    Artisan::call('optimize:clear');
                    Artisan::call('config:clear');
                    Artisan::call('view:clear');

                    $message = "Package {$displayName} Uninstalled Successfully.";
                } else {
                    $message = "Composer failed. Output:\n" . $output;
                    return response()->json([
                        'status' => 'error',
                        'message' => $message
                    ], 500);
                }
            } else {
                // Install dependencies FIRST
                $this->installDependentPackage($vendor, $packageKey, $industry);

                // Now install the main package
                $command = "composer require {$vendor}/{$package}:@dev";
                ob_start();
                passthru($command, $exitCode);
                $output = ob_get_clean();

                if ($exitCode === 0) {
                    Artisan::call('optimize:clear');
                    // Run migrations for the package
                    Artisan::call('migrate', [
                        '--path' => "vendor/{$vendor}/{$package}/database/migrations",
                        '--force' => true,
                    ]);

                    // Run seeders if package exists
                    $seeders = [
                        'settings'                  => 'Admin\Settings\Database\Seeders\SettingSeeder',
                        'users'                     => 'Admin\Users\Database\Seeders\SeedUserRolesSeeder',
                        'admin_role_permissions'    => 'Admin\AdminRolePermissions\Database\Seeders\AdminRolePermissionDatabaseSeeder',
                        'emails'                    => 'Admin\Emails\Database\Seeders\MailDatabaseSeeder',
                        'shipping_charges'          => 'Admin\ShippingCharges\Database\Seeders\ShippingZoneSeeder',
                    ];

                    foreach ($seeders as $pkg => $seederClass) {
                        if (is_dir(base_path("vendor/admin/{$pkg}"))) {
                            Artisan::call('db:seed', [
                                '--class' => $seederClass,
                                '--force' => true,
                            ]);
                        }
                    }

                    // Update DB status
                    $this->updatePackageStatus($vendor, $package, true);

                    $displayName = config("constants.package_display_names.$packageKey", $packageKey);
                    $message = "Package {$displayName} Installed Successfully.";
                } else {
                    $message = "Composer failed. Output:\n" . $output;
                    return response()->json([
                        'status' => 'error',
                        'message' => $message
                    ], 500);
                }
            }

            // Return JSON if requested
            if ($request->expectsJson()) {
                return response()->json(['status' => 'success', 'message' => $message]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Install all dependencies recursively
     */
    private function installDependentPackage($vendor, $packageKey, $industry)
    {

        if (!isset($this->dependencyMapForInstall[$packageKey])) {
            return;
        }
        $packages = $this->dependencyMapForInstall[$packageKey];

        // If industry-specific dependencies
        if (is_array($packages) && isset($packages[$industry])) {
            $packages = $packages[$industry];
        }

        $packages = collect($packages)->flatten()->unique()->toArray();

        foreach ($packages as $depPackage) {
            $path = base_path("vendor/{$vendor}/{$depPackage}");

            if (!is_dir($path)) {
                // Recursive call for this specific dependent package
                $this->installDependentPackage($vendor, $depPackage, $industry);

                $command = "composer require {$vendor}/{$depPackage}:@dev";
                ob_start();
                passthru($command, $exitCode);
                ob_end_clean();

                if ($exitCode !== 0) {
                    throw new \Exception("Failed to install dependency package: {$vendor}/{$depPackage}");
                }

                Artisan::call('optimize:clear');
                Artisan::call('migrate', [
                    '--path' => "vendor/{$vendor}/{$depPackage}/database/migrations",
                    '--force' => true,
                ]);

                $this->updatePackageStatus($vendor, $depPackage, true);
            }
        }
    }

    /**
     * Uninstall dependencies in reverse order recursively
     */
    private function uninstallDependentPackage($vendor, $packageKey, $industry)
    {
        if (!isset($this->dependencyMapForUnInstall[$packageKey])) {
            return;
        }

        $packages = $this->dependencyMapForUnInstall[$packageKey];

        // Use industry-specific dependencies if available
        if (is_array($packages) && isset($packages[$industry])) {
            $packages = $packages[$industry];
        }

        // Flatten, reverse, and make unique to ensure child tables uninstall first
        $packages = collect($packages)->flatten()->reverse()->unique()->toArray();

        foreach ($packages as $package) {
            $path = base_path("vendor/{$vendor}/{$package}");

            // Recursive uninstall
            $this->uninstallDependentPackage($vendor, $package, $industry);

            if (is_dir($path)) {
                $command = "composer remove {$vendor}/{$package}";
                ob_start();
                passthru($command, $exitCode);
                ob_end_clean();

                // Remove published files & DB tables safely
                $this->removePublishedFiles($vendor, $package, $industry);

                // Update package status
                $this->updatePackageStatus($vendor, $package, false);
            }
        }
    }

    /**
     * Remove published files and drop tables safely
     */
    protected function removePublishedFiles($vendor, $package, $industry = null)
    {
        $industry ??= DB::table('settings')->where('slug', 'industry')->value('config_value') ?? 'ecommerce';
        $packageKey = "{$vendor}/{$package}";

        // Recursively remove dependent packages first
        if (isset($this->dependencyMapForUnInstall[$packageKey])) {
            $dependencies = $this->dependencyMapForUnInstall[$packageKey];

            if (is_array($dependencies) && isset($dependencies[$industry])) {
                $dependencies = $dependencies[$industry];
            }

            $dependencies = collect($dependencies)->flatten()->unique()->reverse()->toArray();

            foreach ($dependencies as $dep) {
                $this->removePublishedFiles($vendor, $dep, $industry); // Recursive uninstall
            }
        }

        // Remove published files
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

        // Package-specific tables and migrations
        $tables = [];
        $migrations = [];
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
                $tables = ['course_category', 'course_purchases', 'course_sections', 'transactions', 'lectures', 'courses', 'quiz_answers', 'quiz_questions', 'quizzes', 'ratings', 'wishlists'];
                $migrations = [
                    'create_courses_table',
                    'create_course_category_table',
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
                // Drop the columns added by the old migration if they exist
                if (Schema::hasTable('orders')) {
                    Schema::table('orders', function ($table) {
                        if (Schema::hasColumn('orders', 'coupon_id')) {
                            $table->dropForeign(['coupon_id']);
                            $table->dropColumn(['coupon_id', 'discount_value']);
                        }
                    });
                }else if (Schema::hasTable('course_purchases')) {
                    Schema::table('course_purchases', function ($table) {
                        if (Schema::hasColumn('course_purchases', 'coupon_id')) {
                            $table->dropForeign(['coupon_id']);
                            $table->dropColumn(['coupon_id', 'discount_value']);
                        }
                    });
                }
                $tables = ['coupon_category', 'coupon_course', 'coupon_product', 'coupons'];
                $migrations = [
                    'create_coupon_category_table',
                    'create_coupon_course_table',
                    'create_coupon_product_table',
                    'add_discount_fields_to_orders_table',
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
            case 'tags':
                $industry = DB::table('settings')->where('slug', 'industry')->value('config_value') ?? 'ecommerce';
                if ($industry == 'education') {
                    $tables = ['course_tag', 'tags'];
                } else {
                    $tables = ['product_tags', 'tags'];
                }
                $migrations = [
                    'create_course_product_tag_table',
                    'create_tags_table',
                ];
                break;
            case 'product_transactions':
                $tables = ['transactions'];
                $migrations = [
                    'create_transactions_table',
                ];
                break;
            case 'product_return_refunds':
                $tables = ['return_refund_requests'];
                $migrations = [
                    'create_return_refund_requests_table',
                ];
                break;
            case 'course_transactions':
                $tables = ['transactions', 'course_purchases'];
                $migrations = [
                    'create_transactions_table',
                    'create_course_purchases_table',
                ];
                break;
            case 'commissions':
                // Drop the columns added by the old migration if they exist
                if (Schema::hasTable('orders')) {
                    Schema::table('orders', function ($table) {
                        if (Schema::hasColumn('orders', 'commission_id')) {
                            $table->dropForeign(['commission_id']);
                            $table->dropColumn(['commission_id', 'commission_type', 'commission_value']);
                        }
                    });
                }else if (Schema::hasTable('course_purchases')) {
                    Schema::table('course_purchases', function ($table) {
                        if (Schema::hasColumn('course_purchases', 'commission_id')) {
                            $table->dropForeign(['commission_id']);
                            $table->dropColumn(['commission_id', 'commission_type', 'commission_value']);
                        }
                    });
                }
                $tables = ['commission_category', 'commissions'];
                $migrations = [
                    'add_commission_fields_to_orders_table',
                    'create_commission_category_table',
                    'create_commissions_table',
                ];
                break;
            default:
                $tables = [$package];
                $migrations = ['create_' . $package . '_table'];
                break;
        }

        // Drop foreign keys first to avoid SQL errors
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME, TABLE_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE REFERENCED_TABLE_NAME = '{$table}' 
                AND TABLE_SCHEMA = DATABASE()
            ");
                foreach ($foreignKeys as $fk) {
                    Schema::table($fk->TABLE_NAME, function ($t) use ($fk) {
                        $t->dropForeign($fk->CONSTRAINT_NAME);
                    });
                }
            }
        }

        // Drop tables
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        // Remove migrations
        foreach ($migrations as $migration) {
            DB::table('migrations')
                ->where('migration', 'like', '%' . $migration . '%')
                ->delete();
        }

        // Update package status
        $this->updatePackageStatus($vendor, $package, false);
    }


    /**
     * Update package installation status in the packages table
     */
    private function updatePackageStatus($vendor, $package, $isInstalled = true)
    {
        $packageName = "{$vendor}/{$package}";
        try {
            $packageRecord = Package::where('package_name', $packageName)->first();

            if (!$packageRecord) {
                Log::warning("Package record not found: {$packageName}");
                return;
            }

            $packageRecord->update([
                'is_installed' => $isInstalled,
                'installed_at' => $isInstalled ? now() : null,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the installation process
            Log::error("Failed to update package status for {$packageName}: " . $e->getMessage());
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
