<?php

namespace App\Services;

use App\Models\Order;

class OrderService extends Service
{
    protected $orderModel;

    public function __construct(Order $orderModel)
    {
        $this->orderModel = $orderModel;
    }
}
