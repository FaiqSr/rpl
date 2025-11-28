<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends BaseModel
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'category_id',
        'unit'
    ];

    public function orderDetail(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'product_id', 'product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'product_id')->orderBy('created_at', 'desc');
    }

    public function validReviews(): HasMany
    {
        // Only reviews with valid order_id (order still exists)
        return $this->hasMany(ProductReview::class, 'product_id', 'product_id')
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->orderBy('created_at', 'desc');
    }

    public function getAverageRatingAttribute(): float
    {
        // Only count top-level reviews (not replies) with rating > 0 and valid order_id
        // Always use query to ensure order exists (more reliable than checking loaded relation)
        $avg = $this->reviews()
            ->whereNull('parent_id')
            ->where('rating', '>', 0)
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->avg('rating');
        return $avg !== null ? (float) $avg : 0.0;
    }

    public function getTotalReviewsAttribute(): int
    {
        // Only count top-level reviews (not replies) with rating > 0 and valid order_id
        // Always use query to ensure order exists (more reliable than checking loaded relation)
        return $this->reviews()
            ->whereNull('parent_id')
            ->where('rating', '>', 0)
            ->whereNotNull('order_id')
            ->whereHas('order')
            ->count();
    }
}
