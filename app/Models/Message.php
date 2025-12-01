<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory;

    protected $primaryKey = 'message_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'message_id',
        'chat_id',
        'sender_id',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->message_id)) {
                $model->message_id = (string) Str::uuid();
            }
        });
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id', 'chat_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}
