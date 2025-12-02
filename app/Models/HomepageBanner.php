<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class HomepageBanner extends BaseModel
{
    use HasFactory;

    protected $table = 'homepage_banners';
    protected $primaryKey = 'banner_id';
    protected $fillable = [
        'banner_id',
        'title',
        'image_url',
        'banner_type',
        'link_url',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->banner_id)) {
                $model->banner_id = (string) Str::uuid();
            }
        });
    }
}
