<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Cache;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function guest_cannot_create_product()
    {
        $productData = [
            'name' => 'Nuevo Producto',
            'description' => 'Descripción del producto',
            'price' => 299.99,
            'stock' => 50
        ];

        $response = $this->postJson('/api/products', $productData);
        
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_create_product()
    {
        Sanctum::actingAs($this->user);

        $productData = [
            'name' => 'Nuevo Producto',
            'description' => 'Descripción del producto',
            'price' => 299.99,
            'stock' => 50
        ];

        $response = $this->postJson('/api/products', $productData);
        
        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'Producto creado exitosamente',
                     'product' => [
                         'name' => 'Nuevo Producto',
                         'description' => 'Descripción del producto',
                         'price' => 299.99,
                         'stock' => 50
                     ]
                 ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Nuevo Producto',
            'price' => 299.99,
            'stock' => 50
        ]);
    }

    /** @test */
    public function create_product_validates_required_fields()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/products', []);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'description', 'price', 'stock']);
    }

    /** @test */
    public function create_product_validates_unique_name()
    {
        Sanctum::actingAs($this->user);

        Product::factory()->create(['name' => 'Producto Existente']);

        $productData = [
            'name' => 'Producto Existente',
            'description' => 'Descripción del producto',
            'price' => 299.99,
            'stock' => 50
        ];

        $response = $this->postJson('/api/products', $productData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function authenticated_user_can_update_product()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create([
            'name' => 'Producto Original',
            'price' => 100.00,
            'stock' => 10
        ]);

        $updateData = [
            'name' => 'Producto Actualizado',
            'price' => 150.00,
            'stock' => 20
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Producto actualizado exitosamente',
                     'product' => [
                         'name' => 'Producto Actualizado',
                         'price' => 150.00,
                         'stock' => 20
                     ]
                 ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Producto Actualizado',
            'price' => 150.00,
            'stock' => 20
        ]);
    }

    /** @test */
    public function update_product_validates_unique_name_except_self()
    {
        Sanctum::actingAs($this->user);

        $product1 = Product::factory()->create(['name' => 'Producto 1']);
        $product2 = Product::factory()->create(['name' => 'Producto 2']);

        // Actualizar con el mismo nombre (debe permitirse)
        $response = $this->putJson("/api/products/{$product1->id}", [
            'name' => 'Producto 1',
            'description' => 'Nueva descripción',
            'price' => 100.00,
            'stock' => 10
        ]);
        $response->assertStatus(200);

        // Actualizar con nombre de otro producto (debe fallar)
        $response = $this->putJson("/api/products/{$product1->id}", [
            'name' => 'Producto 2',
            'description' => 'Nueva descripción',
            'price' => 100.00,
            'stock' => 10
        ]);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function authenticated_user_can_soft_delete_product()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Producto eliminado exitosamente'
                 ]);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        
        // Verificar que el producto no aparece en consultas normales
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function authenticated_user_can_restore_deleted_product()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create();
        $product->delete(); // Soft delete

        $response = $this->postJson("/api/products/{$product->id}/restore");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Producto restaurado exitosamente'
                 ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function authenticated_user_can_force_delete_product()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create();
        $product->delete(); // Soft delete primero

        $response = $this->deleteJson("/api/products/{$product->id}/force");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Producto eliminado permanentemente'
                 ]);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function guest_cannot_update_product()
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Producto Actualizado'
        ]);
        
        $response->assertStatus(401);
    }

    /** @test */
    public function guest_cannot_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");
        
        $response->assertStatus(401);
    }

    /** @test */
    public function operations_invalidate_cache()
    {
        Sanctum::actingAs($this->user);

        // Llenar cache
        Cache::put('products.all', 'cached_data', 600);
        Cache::put('product_1', 'cached_product', 600);

        $product = Product::factory()->create();

        // Crear producto
        $this->postJson('/api/products', [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'stock' => 10
        ]);

        $this->assertNull(Cache::get('products.all'));

        // Restaurar cache para siguiente test
        Cache::put('products.all', 'cached_data', 600);
        Cache::put("product_{$product->id}", 'cached_product', 600);

        // Actualizar producto
        $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Product'
        ]);

        $this->assertNull(Cache::get('products.all'));
        $this->assertNull(Cache::get("product_{$product->id}"));
    }

    /** @test */
    public function cannot_restore_non_deleted_product()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create();

        $response = $this->postJson("/api/products/{$product->id}/restore");
        
        $response->assertStatus(404);
    }

    /** @test */
    public function cannot_force_delete_non_deleted_product()
    {
        Sanctum::actingAs($this->user);

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}/force");
        
        $response->assertStatus(404);
    }

    /** @test */
    public function unique_validation_ignores_soft_deleted_products()
    {
        Sanctum::actingAs($this->user);

        // Crear producto y eliminarlo (soft delete)
        $deletedProduct = Product::factory()->create(['name' => 'Producto Eliminado']);
        $deletedProduct->delete();

        // Crear nuevo producto con el mismo nombre debe funcionar
        $productData = [
            'name' => 'Producto Eliminado',
            'description' => 'Descripción del producto',
            'price' => 299.99,
            'stock' => 50
        ];

        $response = $this->postJson('/api/products', $productData);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseCount('products', 2); // Uno eliminado, uno activo
    }
}
