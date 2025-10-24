<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleService extends Service
{
    protected $articleModel;

    public function __construct(Article $articleModel)
    {
        $this->articleModel = $articleModel;
    }

    public function getArticle(Request $request) {}
}
