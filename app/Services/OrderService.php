<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
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
                // Usar caché para obtener productos
                $product = Cache::remember("product_{$productData['id']}", 600, function () use ($productData) {
                    return Product::findOrFail($productData['id']);
                });
                
                $subtotal = $product->price * $productData['quantity'];
                $order->products()->attach($product->id, [
                    'quantity' => $productData['quantity'],
                    'price' => $product->price,
                ]);
                $total += $subtotal;
                
                // Invalidar caché del producto si el stock cambió
                Cache::forget("product_{$product->id}");
            }
            
            $order->update(['total' => $total]);
            
            // Invalidar caché relacionado con órdenes del usuario
            Cache::forget("user_orders_{$data['user_id']}");
            Cache::forget('products.all'); // Invalidar lista de productos por posibles cambios de stock
            
            SendOrderCreatedEmail::dispatch($order);
            return $order->load(['user', 'address', 'products']);
        });
    }

    public function getUserOrders($userId)
    {
        return Cache::remember("user_orders_{$userId}", 300, function () use ($userId) {
            return Order::where('user_id', $userId)
                ->with(['products', 'address'])
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    public function getOrderStats()
    {
        return Cache::remember('order_stats', 1800, function () {
            return [
                'total_orders' => Order::count(),
                'pending_orders' => Order::where('status', 'pending')->count(),
                'completed_orders' => Order::where('status', 'completed')->count(),
                'total_revenue' => Order::where('status', 'completed')->sum('total'),
            ];
        });
    }
}
