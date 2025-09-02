<?php

namespace admin\admin_auth\Models;

use admin\admin_role_permissions\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Kyslik\ColumnSortable\Sortable;

if (!trait_exists(\admin\admin_role_permissions\Traits\HasRoles::class)) {
    trait HasRolesFallback {}
} else {
    class_alias(
        \admin\admin_role_permissions\Traits\HasRoles::class,
        __NAMESPACE__ . '\HasRolesFallback'
    );
}

class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes, HasRolesFallback, Sortable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'mobile',
        'website_name',
        'website_slug',
        'industry',
        'is_dummy_data',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $sortable = [
        'name' => 'sortableName',
        'email',
        'status',
        'created_at',
    ];

    public static function sortableName($query, $direction)
    {
        return $query->orderByRaw("CONCAT(first_name, ' ', last_name) $direction");
    }

    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getRoleNameAttribute()
    {
        return $this->roles->pluck('name')->first(); // or join with comma if multiple roles
    }

    public function getRoleAttribute()
    {
        return $this->roles->pluck('name')->implode(', ');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            if (empty($admin->website_slug)) {
                $admin->website_slug = Str::slug($admin->website_name);
            }
        });

        static::updating(function ($admin) {
            if (empty($admin->website_slug)) {
                $admin->website_slug = Str::slug($admin->website_name);
            }
        });
    }

    public function scopeFilter($query, $name)
    {
        if ($name) {
            return $query->where(function ($q) use ($name) {
                // full name filter
                $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", '%' . $name . '%')
                    ->orWhere('email', 'like', '%' . $name . '%')
                    ->orWhere('mobile', 'like', '%' . $name . '%')
                    ->orWhere('first_name', 'like', '%' . $name . '%')
                    ->orWhere('last_name', 'like', '%' . $name . '%');
            });
        }
        return $query;
    }
    /**
     * filter by status
     */
    public function scopeFilterByStatus($query, $status)
    {
        if (!is_null($status)) {
            return $query->where('status', $status);
        }

        return $query;
    }

    public function getFullNameAttribute()
    {
        $first = trim($this->first_name ?? '');
        $last = trim($this->last_name ?? '');
        return trim("{$first} {$last}");
    }

    public static function getPerPageLimit(): int
    {
        return Config::has('get.admin_page_limit')
            ? Config::get('get.admin_page_limit')
            : 10;
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_admin',      // pivot table name
            'admin_id',        // foreign key on pivot table for this model
            'role_id'          // foreign key on pivot table for Role model
        );
    }
}
