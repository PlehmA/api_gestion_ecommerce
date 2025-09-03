<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;

class ProductCacheTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that product creation works without cache tagging errors
     */
    public function test_product_creation_without_cache_errors(): void
    {
        $productData = [
            'name' => 'Producto de Prueba',
            'description' => 'Descripción de prueba del producto',
            'price' => 99.99,
            'stock' => 10
        ];

        // Crear producto directamente sin pasar por autenticación para simplificar
        $product = Product::create($productData);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Producto de Prueba', $product->name);
        $this->assertEquals(99.99, $product->price);
        $this->assertEquals(10, $product->stock);
    }

    /**
     * Test Prueba de cache
     */
    public function test_product_controller_cache_operations(): void
    {
        $productData = [
            'name' => 'Producto Controller Test',
            'description' => 'Descripción de prueba',
            'price' => 149.99,
            'stock' => 25
        ];

        // Simular la operación del controller sin la validación HTTP
        $product = Product::create($productData);
        
        // Simular las operaciones de cache que hace el controller
        $controller = new \App\Http\Controllers\ProductController();
        
        // Utilizar reflexión para acceder al método privado
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('clearProductsCache');
        $method->setAccessible(true);
        
        // Esto no debería arrojar errores de cache tagging
        $this->assertNull($method->invoke($controller));
    }
}
