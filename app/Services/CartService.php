<?php

namespace App\Services;

use App\Models\Cart;

class CartService extends Service
{
    protected $cartModel;

    public function __construct(Cart $cartModel)
    {
        $this->cartModel = $cartModel;
    }
}
