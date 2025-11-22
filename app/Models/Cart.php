<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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

    public function getCartByUserId($userId)
    {
        $query = DB::table("$this->table as c")->join('products as p', 'c.product_id', '=', 'p.product_id')->join('product_images as pi', 'p.product_id', '=', 'pi.product_id')->select([
            'c.qty',
            'p.slug',
            'pi.name',
            'pi.url',
            'c.updated_at'
        ])->where('c.user_id', $userId)->get();

        return $query;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
