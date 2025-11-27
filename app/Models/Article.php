<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends BaseModel
{
    use HasFactory;

    protected $table = 'articles';
    protected $primaryKey = 'article_id';
    protected $fillable = [
        'article_id',
        'author_id',
        'category_id',
        'title',
        'content'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->article_id)) {
                $model->article_id = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function comment(): HasMany
    {
        return $this->hasMany(Comment::class, 'article_id', 'article_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id', 'category_id');
    }
}
