<?php

namespace App\Services;

use App\Models\Cart;
use Exception;

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

    public function deleteCartByUserId($cartId)
    {
        if (!$cartId) {
            return $this->jsonErrorResponse('data tidak valid');
        }
        return $this->cartModel->deleteCartByUserId($cartId);
    }

    public function updateQtyCartById($cartId, int $qty)
    {
        $data = [
            'cartId' => $cartId,
            'qty' => $qty
        ];
        $query = $this->cartModel->updateQtyCartById($data);
        if ($query) {
            return $this->jsonResponse(['message' => "Berhasil mengubah quantity cart"]);
        }
        return $this->jsonErrorResponse('Gagal mengubah cart');
    }
}
