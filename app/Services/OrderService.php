<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendOrderCreatedEmail;

class OrderService
{
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'user_id' => $data['user_id'],
                'address_id' => $data['address_id'],
                'total' => 0,
                'status' => 'pending',
            ]);
            $total = 0;
            foreach ($data['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                $subtotal = $product->price * $productData['quantity'];
                $order->products()->attach($product->id, [
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                ]);
                $total += $subtotal;
            }
            $order->update(['total' => $total]);
            SendOrderCreatedEmail::dispatch($order);
            return $order->load(['user', 'address', 'products']);
        });
    }
}
