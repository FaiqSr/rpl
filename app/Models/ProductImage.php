<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends BaseModel
{
    use HasFactory;

    protected $table = 'product_images';
    protected $primaryKey = 'product_image_id';
    protected $fillable = ['product_id', 'name', 'url'];


    public function products(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
