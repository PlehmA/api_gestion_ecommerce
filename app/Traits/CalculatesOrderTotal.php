<?php

namespace App\Traits;

trait CalculatesOrderTotal
{
    public function calculateTotal($products)
    {
        $total = 0;
        foreach ($products as $product) {
            $total += $product['price'] * $product['quantity'];
        }
        return $total;
    }
}
