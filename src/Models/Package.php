<?php

namespace admin\admin_auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'package_name',
        'display_name',
        'vendor',
        'name',
        'package_type',
        'industry',
        'description',
        'is_installed',
        'is_auto_install',
        'installed_at',
    ];

    protected $casts = [
        'is_installed' => 'boolean',
        'is_auto_install' => 'boolean',
        'installed_at' => 'datetime',
    ];

    /**
     * Scope to get only installed packages
     */
    public function scopeInstalled($query)
    {
        return $query->where('is_installed', true);
    }

    /**
     * Scope to get only auto-install packages
     */
    public function scopeAutoInstall($query)
    {
        return $query->where('is_auto_install', true);
    }

    /**
     * Scope to get only active packages (non-auto-install)
     */
    public function scopeActive($query)
    {
        return $query->where('is_auto_install', false);
    }

    /**
     * Scope to get common packages
     */
    public function scopeCommon($query)
    {
        return $query->where('package_type', 'common');
    }

    /**
     * Scope to get auto-install packages
     */
    public function scopeAutoInstallType($query)
    {
        return $query->where('package_type', 'auto_install');
    }

    /**
     * Scope to get industry-specific packages
     */
    public function scopeIndustry($query)
    {
        return $query->where('package_type', 'industry');
    }

    /**
     * Scope to get packages for a specific industry
     */
    public function scopeForIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Mark package as installed
     */
    public function markAsInstalled()
    {
        $this->update([
            'is_installed' => true,
            'installed_at' => now(),
        ]);
    }

    /**
     * Mark package as uninstalled
     */
    public function markAsUninstalled()
    {
        $this->update([
            'is_installed' => false,
            'installed_at' => null,
        ]);
    }

    /**
     * Get the full package path
     */
    public function getFullPackageNameAttribute()
    {
        return $this->package_name;
    }

    /**
     * Check if package is installed
     */
    public function isInstalled()
    {
        return $this->is_installed;
    }

    /**
     * Check if package is auto-install
     */
    public function isAutoInstall()
    {
        return $this->is_auto_install;
    }
} 