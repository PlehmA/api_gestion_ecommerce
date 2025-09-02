<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\OrderService;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_order_calculates_total_correctly()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product1 = Product::factory()->create(['price' => 100]);
        $product2 = Product::factory()->create(['price' => 50]);

        $service = new OrderService();
        $order = $service->createOrder([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'products' => [
                ['id' => $product1->id, 'quantity' => 2],
                ['id' => $product2->id, 'quantity' => 1],
            ],
        ]);

        $this->assertEquals(250, $order->total);
    }
}
