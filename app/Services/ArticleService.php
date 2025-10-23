<?php

namespace App\Services;

use App\Models\Article;

class ArticleService extends Service
{
    protected $articleModel;

    public function __construct(Article $articleModel)
    {
        $this->articleModel = $articleModel;
    }
}
