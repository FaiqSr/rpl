<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductReview extends BaseModel
{
    use HasFactory;

    protected $table = 'product_reviews';
    protected $primaryKey = 'review_id';
    protected $fillable = [
        'review_id',
        'product_id',
        'user_id',
        'order_id',
        'parent_id',
        'rating',
        'review',
        'image'
    ];

    protected $casts = [
        'rating' => 'integer',
        'image' => 'array', // Cast image as array for multiple images
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->review_id)) {
                $model->review_id = (string) Str::uuid();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductReview::class, 'parent_id', 'review_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'parent_id', 'review_id')->orderBy('created_at', 'asc');
    }
}
