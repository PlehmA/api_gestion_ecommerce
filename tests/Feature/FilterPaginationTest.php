<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class FilterPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario de prueba
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Crear token de autenticación
        $this->token = $this->user->createToken('test_token')->plainTextToken;

        // Crear productos de prueba
        Product::create(['name' => 'Laptop Gaming', 'description' => 'Gaming laptop', 'price' => 1500.00, 'stock' => 10]);
        Product::create(['name' => 'Mouse Gaming', 'description' => 'Gaming mouse', 'price' => 50.00, 'stock' => 25]);
        Product::create(['name' => 'Keyboard', 'description' => 'Mechanical keyboard', 'price' => 100.00, 'stock' => 0]);
        Product::create(['name' => 'Monitor 4K', 'description' => '4K gaming monitor', 'price' => 300.00, 'stock' => 5]);
    }

    public function test_products_pagination()
    {
        $response = $this->getJson('/api/products?page=1&per_page=2');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'current_page',
                     'per_page', 
                     'total',
                     'last_page'
                 ]);

        $data = $response->json();
        $this->assertEquals(1, $data['current_page']);
        $this->assertEquals(2, $data['per_page']);
        $this->assertCount(2, $data['data']);
    }

    public function test_products_filter_by_name()
    {
        $response = $this->getJson('/api/products?filter[name]=gaming');

        $response->assertStatus(200);
        
        $products = $response->json('data');
        foreach ($products as $product) {
            $this->assertStringContainsStringIgnoringCase('gaming', $product['name']);
        }
    }

    public function test_products_filter_by_price_range()
    {
        $response = $this->getJson('/api/products?filter[min_price]=100&filter[max_price]=500');

        $response->assertStatus(200);
        
        $products = $response->json('data');
        foreach ($products as $product) {
            $this->assertGreaterThanOrEqual(100, $product['price']);
            $this->assertLessThanOrEqual(500, $product['price']);
        }
    }

    public function test_products_filter_in_stock()
    {
        $response = $this->getJson('/api/products?filter[in_stock]=true');

        $response->assertStatus(200);
        
        $products = $response->json('data');
        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product['stock']);
        }
    }

    public function test_products_sorting()
    {
        // Ordenar por precio descendente
        $response = $this->getJson('/api/products?sort=-price');

        $response->assertStatus(200);
        
        $products = $response->json('data');
        $prices = array_column($products, 'price');
        
        // Verificar que está ordenado descendentemente
        $sortedPrices = $prices;
        rsort($sortedPrices);
        $this->assertEquals($sortedPrices, $prices);
    }

    public function test_orders_pagination_requires_auth()
    {
        // Sin autenticación debería fallar
        $response = $this->getJson('/api/orders?page=1&per_page=5');
        $response->assertStatus(401);

        // Con autenticación debería funcionar
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/orders?page=1&per_page=5');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'current_page',
                     'per_page',
                     'total'
                 ]);
    }

    public function test_orders_filter_by_status()
    {
        // Crear algunas órdenes de prueba
        Order::create([
            'user_id' => $this->user->id,
            'total' => 100.00,
            'status' => 'pending'
        ]);

        Order::create([
            'user_id' => $this->user->id,
            'total' => 200.00,
            'status' => 'delivered'
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/orders?filter[status]=pending');

        $response->assertStatus(200);
        
        $orders = $response->json('data');
        foreach ($orders as $order) {
            $this->assertEquals('pending', $order['status']);
        }
    }

    public function test_combined_filters_and_pagination()
    {
        $response = $this->getJson('/api/products?filter[name]=gaming&filter[min_price]=50&sort=-price&page=1&per_page=1');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'current_page',
                     'per_page'
                 ]);

        $data = $response->json();
        $this->assertEquals(1, $data['per_page']);
        
        if (count($data['data']) > 0) {
            $product = $data['data'][0];
            $this->assertStringContainsStringIgnoringCase('gaming', $product['name']);
            $this->assertGreaterThanOrEqual(50, $product['price']);
        }
    }
}
