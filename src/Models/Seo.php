<?php

namespace admin\admin_auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Seo extends Model
{
    use HasFactory, Sortable;

    protected $table = 'seo_meta';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'model_name',
        'model_record_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $sortable = [
        'model_name',
        'meta_title',
        'created_at',
    ];
}
