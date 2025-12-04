<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductImage extends BaseModel
{
    use HasFactory;

    protected $table = 'product_images';
    protected $primaryKey = 'product_image_id';
    protected $fillable = ['product_image_id', 'product_id', 'name', 'url'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->product_image_id)) {
                $model->product_image_id = (string) Str::uuid();
            }
        });
    }

    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
