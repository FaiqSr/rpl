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

    public function getAverageRatingAttribute(): float
    {
        // Only count top-level reviews (not replies) with rating > 0
        // Use loaded relationship if available, otherwise query
        if ($this->relationLoaded('reviews')) {
            $topLevelReviews = $this->reviews->whereNull('parent_id')->where('rating', '>', 0);
            if ($topLevelReviews->count() === 0) {
                return 0.0;
            }
            $avg = $topLevelReviews->avg('rating');
            return $avg !== null ? (float) $avg : 0.0;
        }
        $avg = $this->reviews()->whereNull('parent_id')->where('rating', '>', 0)->avg('rating');
        return $avg !== null ? (float) $avg : 0.0;
    }

    public function getTotalReviewsAttribute(): int
    {
        // Only count top-level reviews (not replies) with rating > 0
        // Use loaded relationship if available, otherwise query
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->whereNull('parent_id')->where('rating', '>', 0)->count();
        }
        return $this->reviews()->whereNull('parent_id')->where('rating', '>', 0)->count();
    }
}
