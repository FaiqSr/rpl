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

    public function getCartByUserId($userId)
    {
        $data = $this->cartModel->getCartByUserId($userId);
        if (!$data) {
            return $this->jsonErrorResponse(
                'Data tidak ada'
            );
        }
        return $this->jsonResponse($data, 200);
    }
}
