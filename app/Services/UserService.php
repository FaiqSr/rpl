<?php

namespace App\Services;

use App\Models\User;

class UserService extends Service
{
    protected $userModel;

    function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }
}
