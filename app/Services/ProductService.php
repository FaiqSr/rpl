<?php

namespace App\Services;

use App\Models\Product;

class ProductService extends Service
{

    protected $productModel;

    public function __construct(Product $productModel)
    {
        $this->productModel = $productModel;
    }
}
