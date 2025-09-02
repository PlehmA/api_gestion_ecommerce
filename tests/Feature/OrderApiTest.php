<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_show_order()
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);
        $product = Product::factory()->create(['price' => 100]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/orders', [
            'user_id' => $user->id,
            'address_id' => $address->id,
            'products' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(201);
        $orderId = $response->json('id');

        $response = $this->getJson("/api/orders/{$orderId}");
        $response->assertStatus(200)
            ->assertJsonFragment(['total' => 200]);
    }
}
