<?php

namespace Admin\AdminAuth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the selected industry from session
        $selectedIndustry = Session::get('industry', 'ecommerce');
        
        // Get installed packages from session
        $installedPackages = Session::get('installed_packages', []);
        
        // Get package display names and info from config
        $displayNameMap = config('constants.package_display_names', []);
        $packageInfoMap = config('constants.package_info', []);
        $autoInstallPackages = config('constants.auto_install_packages', []);
        $commonPackages = config('constants.common_packages', []);
        $industryPackages = config('constants.industry_packages.' . $selectedIndustry, []);
        
        // Combine all packages that should be in the table
        $allPackages = array_unique(array_merge($autoInstallPackages, $commonPackages, $industryPackages));
        
        // Clear existing packages data
        DB::table('packages')->truncate();
        
        foreach ($allPackages as $fullPackageName) {
            [$vendorName, $packageName] = explode('/', $fullPackageName);
            
            $displayName = $displayNameMap[$fullPackageName] ?? $packageName;
            $packageInfo = $packageInfoMap[$fullPackageName] ?? [];
            $description = $packageInfo['description'] ?? null;
            
            // Determine package type
            $packageType = in_array($fullPackageName, $autoInstallPackages) ? 'auto_install' : 
                          (in_array($fullPackageName, $commonPackages) ? 'common' : 'industry');
            $industry = $packageType === 'industry' ? $selectedIndustry : null;
            
            // Check if package is installed
            $isInstalled = in_array($fullPackageName, $installedPackages);
            
            // Set is_auto_install flag
            $isAutoInstall = in_array($fullPackageName, $autoInstallPackages);
            
            DB::table('packages')->insert([
                'package_name' => $fullPackageName,
                'display_name' => $displayName,
                'vendor' => $vendorName,
                'name' => $packageName,
                'package_type' => $packageType,
                'industry' => $industry,
                'description' => $description,
                'is_installed' => $isInstalled,
                'is_auto_install' => $isAutoInstall,
                'installed_at' => $isInstalled ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
} 