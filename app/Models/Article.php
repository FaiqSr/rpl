<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends BaseModel
{
    use HasFactory;

    protected $table = 'articles';
    protected $primaryKey = 'article_id';
    protected $fillable = [
        'author_id',
        'title',
        'content'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class, 'article_id', 'article_id');
    }
}
