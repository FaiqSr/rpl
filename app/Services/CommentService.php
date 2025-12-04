<?php

namespace App\Services;

use App\Models\Comment;

class CommentService extends Service
{
    protected $commentModel;

    public function __construct(Comment $commentModel)
    {
        $this->commentModel = $commentModel;
    }
}
