<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends BaseModel
{
    use HasFactory;


    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'notes',
        'buyer_name',
        'buyer_phone',
        'buyer_address',
        'shipping_service',
        'payment_method',
        'tracking_number',
        'payment_status',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
        
        // Auto-create chat when order is created
        static::created(function ($order) {
            try {
                // Get seller (first seller user)
                $seller = \App\Models\User::where('role', 'seller')->first();
                
                if ($seller) {
                    // Check if chat already exists for this order
                    $existingChat = \App\Models\Chat::where('order_id', $order->order_id)->first();
                    
                    if (!$existingChat) {
                        \App\Models\Chat::create([
                            'buyer_id' => $order->user_id,
                            'seller_id' => $seller->user_id,
                            'order_id' => $order->order_id,
                            'last_message' => 'Pesanan baru telah dibuat',
                            'last_message_at' => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail order creation
                \Illuminate\Support\Facades\Log::error('Failed to create chat for order: ' . $e->getMessage());
            }
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function orderDetail(): HasMany
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }
}
