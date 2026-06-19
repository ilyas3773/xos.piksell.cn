<?php
declare(strict_types=1);

namespace app\admin\service;

use app\model\Card;
use app\model\Product;

class ProductService
{
    public function syncStock(int $productId): void
    {
        $stock = Card::where('product_id', $productId)
            ->where('status', 'unused')
            ->count();

        Product::where('id', $productId)->update([
            'stock' => $stock,
        ]);
    }
}

