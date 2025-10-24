<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function CartByUserJson(Request $request)
    {

        return $this->cartService->getCartByUserId('81193245-c73c-4da1-b243-8ea16ee42e2c');
    }
}
