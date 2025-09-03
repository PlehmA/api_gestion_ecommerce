<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuickResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_response_format()
    {
        // Crear algunos productos
        Product::create(['name' => 'Test 1', 'description' => 'Desc 1', 'price' => 100, 'stock' => 10]);
        Product::create(['name' => 'Test 2', 'description' => 'Desc 2', 'price' => 200, 'stock' => 5]);

        $response = $this->getJson('/api/products?page=1&per_page=1');

        $response->assertStatus(200);
        
        // Imprimir la estructura de respuesta para debug
        echo "\n=== RESPUESTA COMPLETA ===\n";
        echo json_encode($response->json(), JSON_PRETTY_PRINT);
        echo "\n========================\n";
        
        $this->assertTrue(true);
    }
}
