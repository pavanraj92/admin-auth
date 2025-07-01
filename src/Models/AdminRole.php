<?php

namespace admin\admin_auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AdminRole extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($admin) {
            if (empty($admin->slug)) {
                $admin->slug = Str::slug($admin->name);
            }
        });

        static::updating(function ($admin) {
            if (empty($admin->slug)) {
                $admin->slug = Str::slug($admin->name);
            }
        });
    }
}
