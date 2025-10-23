<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends BaseModel
{
    use HasFactory;
    protected $table = "carts";
    protected $fillable = [
        'user_id',
        'product_id',
        'qty'
    ];

    protected $primaryKey = 'cart_id';

    public function getCartByUserId($userId): Cart
    {
        return $this->where(User::class, '=', $userId)->first();
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo('user_id', 'user_id', 'users');
    }

    public function products(): BelongsTo
    {
        return $this->belongsTo('product_id', 'product_id', 'products');
    }
}
