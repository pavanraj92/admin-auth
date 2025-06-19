<?php

namespace admin\admin_auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    use HasFactory;

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
        'website_name',
        'website_slug'
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

}
