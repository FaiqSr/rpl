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
                // Cek apakah buyer sudah punya general chat dengan admin
                $existingGeneralChat = \App\Models\Chat::where('buyer_id', $order->user_id)
                    ->whereNull('order_id')
                    ->first();
                
                if ($existingGeneralChat) {
                    // Jika sudah ada general chat, gunakan admin yang sama
                    $sellerId = $existingGeneralChat->seller_id;
                } else {
                    // Jika belum ada, pilih admin dengan chat paling sedikit
                    $admins = \App\Models\User::where('role', 'admin')->get();
                    if ($admins->isEmpty()) {
                        $admins = \App\Models\User::where('role', 'seller')->get();
                    }
                    
                    if ($admins->isEmpty()) {
                        return; // No admin found, skip chat creation
                    }
                    
                    // Jika hanya ada 1 admin, langsung gunakan
                    if ($admins->count() === 1) {
                        $sellerId = $admins->first()->user_id;
                    } else {
                        // Hitung jumlah chat per admin dan pilih yang paling sedikit
                        $adminChatCounts = $admins->map(function($admin) {
                            $chatCount = \App\Models\Chat::where('seller_id', $admin->user_id)->count();
                            return [
                                'admin' => $admin,
                                'chat_count' => $chatCount
                            ];
                        });
                        
                        $selected = $adminChatCounts->sortBy('chat_count')->first();
                        $sellerId = $selected['admin']->user_id;
                    }
                }
                
                // Check if chat already exists for this order
                $existingChat = \App\Models\Chat::where('order_id', $order->order_id)->first();
                
                if (!$existingChat) {
                    \App\Models\Chat::create([
                        'buyer_id' => $order->user_id,
                        'seller_id' => $sellerId,
                        'order_id' => $order->order_id,
                        'last_message' => 'Pesanan baru telah dibuat',
                        'last_message_at' => now(),
                    ]);
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

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'order_id', 'order_id');
    }
}
