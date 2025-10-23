<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends BaseModel
{
    use HasFactory;
    protected $table = 'comments';
    protected $primaryKey = 'comment_id';

    protected $fillable = [
        'article_id',
        'user_id',
        'content',
        'comment_id'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id', 'article_id');
    }
}
