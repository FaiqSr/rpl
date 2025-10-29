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

    public function cartByUserJson(Request $request)
    {
        $token = $request->header('Authorization');

        return $this->cartService->jsonResponse(['token' => $token]);

        return $this->cartService->getCartByUserId('81193245-c73c-4da1-b243-8ea16ee42e2c');
    }

    public function updateQtyCartByIdJson(Request $request)
    {
        $request->validate([
            'qty' => 'integer|min:0|required',
            'cart_id' => 'string|required'
        ]);

        return $this->cartService->updateQtyCartById($request->cart_id, $request->qty);
    }
}
