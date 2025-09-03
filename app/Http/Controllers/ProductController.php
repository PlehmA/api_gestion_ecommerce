<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Obtener lista de productos con filtros y paginación avanzada",
     *     description="Lista todos los productos activos (no eliminados) con capacidades de filtrado, ordenamiento y paginación usando Spatie Query Builder. Soporta cache inteligente para mejor rendimiento.",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="filter[name]",
     *         in="query",
     *         description="Filtrar por nombre (búsqueda parcial, insensible a mayúsculas)",
     *         @OA\Schema(type="string"),
     *         example="iPhone"
     *     ),
     *     @OA\Parameter(
     *         name="filter[price]",
     *         in="query",
     *         description="Filtrar por precio exacto",
     *         @OA\Schema(type="number", format="float"),
     *         example=999.99
     *     ),
     *     @OA\Parameter(
     *         name="filter[min_price]",
     *         in="query",
     *         description="Precio mínimo (incluye el valor especificado)",
     *         @OA\Schema(type="number", format="float"),
     *         example=500.00
     *     ),
     *     @OA\Parameter(
     *         name="filter[max_price]",
     *         in="query",
     *         description="Precio máximo (incluye el valor especificado)",
     *         @OA\Schema(type="number", format="float"),
     *         example=2000.00
     *     ),
     *     @OA\Parameter(
     *         name="filter[in_stock]",
     *         in="query",
     *         description="Solo productos con stock disponible (1 para verdadero, 0 para falso)",
     *         @OA\Schema(type="integer", enum={0, 1}),
     *         example=1
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Sintaxis alternativa: filtrar por nombre (búsqueda parcial)",
     *         @OA\Schema(type="string"),
     *         example="smart"
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query", 
     *         description="Sintaxis alternativa: filtrar por precio exacto",
     *         @OA\Schema(type="number", format="float"),
     *         example=999.99
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Sintaxis alternativa: precio mínimo",
     *         @OA\Schema(type="number", format="float"),
     *         example=500.00
     *     ),
     *     @OA\Parameter(
     *         name="max_price", 
     *         in="query",
     *         description="Sintaxis alternativa: precio máximo",
     *         @OA\Schema(type="number", format="float"),
     *         example=2000.00
     *     ),
     *     @OA\Parameter(
     *         name="in_stock",
     *         in="query",
     *         description="Sintaxis alternativa: solo productos en stock (1 ó 0)",
     *         @OA\Schema(type="integer", enum={0, 1}),
     *         example=1
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Campo de ordenamiento. Usar '-' al inicio para orden descendente",
     *         @OA\Schema(
     *             type="string", 
     *             enum={"name", "-name", "price", "-price", "stock", "-stock", "created_at", "-created_at"}
     *         ),
     *         example="-price"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página para paginación",
     *         @OA\Schema(type="integer", minimum=1),
     *         example=1
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Número de productos por página (máximo 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100),
     *         example=15
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de productos con paginación",
     *         @OA\JsonContent(ref="#/components/schemas/ProductPaginated")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación en parámetros de consulta",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Convertir parámetros directos a formato filter para mayor flexibilidad
        $this->convertDirectParamsToFilters($request);
        
        // Crear cache key único basado en todos los parámetros de consulta
        $cacheKey = 'products_filtered_' . md5($request->getQueryString() ?: 'default');
        
        // Si tenemos Redis, usar cache con tags
        if (config('cache.default') === 'redis') {
            $products = Cache::tags(['products'])->remember($cacheKey, 300, function () use ($request) {
                return QueryBuilder::for(Product::class)
                    ->allowedFilters([
                        AllowedFilter::partial('name'),
                        AllowedFilter::exact('price'),
                        AllowedFilter::scope('min_price'),
                        AllowedFilter::scope('max_price'),
                        AllowedFilter::scope('in_stock'),
                    ])
                    ->allowedSorts([
                        'name',
                        'price', 
                        'stock',
                        'created_at'
                    ])
                    ->paginate($request->input('per_page', 15))
                    ->appends($request->query());
            });
        } else {
            // Fallback para drivers sin tagging
            $products = Cache::remember($cacheKey, 300, function () use ($request) {
                return QueryBuilder::for(Product::class)
                    ->allowedFilters([
                        AllowedFilter::partial('name'),
                        AllowedFilter::exact('price'),
                        AllowedFilter::scope('min_price'),
                        AllowedFilter::scope('max_price'),
                        AllowedFilter::scope('in_stock'),
                    ])
                    ->allowedSorts([
                        'name',
                        'price', 
                        'stock',
                        'created_at'
                    ])
                    ->paginate($request->input('per_page', 15))
                    ->appends($request->query());
            });
        }
        
        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Obtener detalle de un producto específico",
     *     description="Obtiene la información completa de un producto por su ID. Utiliza cache por 10 minutos para mejor rendimiento. Solo muestra productos activos (no eliminados lógicamente).",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto a consultar",
     *         @OA\Schema(type="integer", minimum=1),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Información detallada del producto",
     *         @OA\JsonContent(ref="#/components/schemas/ProductDetail")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado o ha sido eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 1")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        // Cache con tagging si Redis está disponible
        if (config('cache.default') === 'redis') {
            $product = Cache::tags(['products'])->remember("product_{$id}", 600, function () use ($id) {
                return Product::findOrFail($id);
            });
        } else {
            $product = Cache::remember("product_{$id}", 600, function () use ($id) {
                return Product::findOrFail($id);
            });
        }
        
        return response()->json($product);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Crear un nuevo producto",
     *     description="Crea un nuevo producto en el catálogo. Requiere autenticación. El nombre debe ser único entre productos activos (ignora productos eliminados). Invalida automáticamente el cache de productos.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del producto a crear",
     *         @OA\JsonContent(ref="#/components/schemas/ProductStore")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto creado exitosamente"),
     *             @OA\Property(property="product", ref="#/components/schemas/ProductDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());
        
        // Invalidar cache de productos
        Cache::forget('products.all');
        // Con Redis, podemos usar cache tagging
        if (config('cache.default') === 'redis') {
            Cache::tags(['products'])->flush();
        } else {
            // Fallback para drivers que no soportan tagging
            $this->clearProductsCache();
        }
        
        return response()->json([
            'message' => 'Producto creado exitosamente',
            'product' => $product
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Actualizar un producto existente",
     *     description="Actualiza la información de un producto específico. Requiere autenticación. Solo se pueden actualizar productos activos. La validación de nombre único ignora el producto actual. Invalida automáticamente el cache.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto a actualizar",
     *         @OA\Schema(type="integer", minimum=1),
     *         example=1
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del producto a actualizar (todos los campos son opcionales)",
     *         @OA\JsonContent(ref="#/components/schemas/ProductUpdate")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto actualizado exitosamente"),
     *             @OA\Property(property="product", ref="#/components/schemas/ProductDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado o eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        
        // Invalidar cache del producto específico y general
        Cache::forget("product_{$id}");
        Cache::forget('products.all');
        // Con Redis, podemos usar cache tagging
        if (config('cache.default') === 'redis') {
            Cache::tags(['products'])->flush();
        } else {
            // Fallback para drivers que no soportan tagging
            $this->clearProductsCache();
        }
        
        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'product' => $product->fresh()
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Eliminar un producto (eliminación lógica)",
     *     description="Realiza una eliminación lógica (soft delete) del producto. El producto no se elimina físicamente de la base de datos, sino que se marca como eliminado. Requiere autenticación. Invalida automáticamente el cache.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto a eliminar",
     *         @OA\Schema(type="integer", minimum=1),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado exitosamente (soft delete)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Producto eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="El producto ya está eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="El producto ya está eliminado. Use la ruta de restauración si desea recuperarlo.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 1")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        // Buscar el producto incluyendo los eliminados para validar el estado
        $product = Product::withTrashed()->findOrFail($id);
        
        // Validar si el producto ya está eliminado
        if ($product->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'El producto ya está eliminado. Use la ruta de restauración si desea recuperarlo.'
            ], 400);
        }
        
        $product->delete(); // Soft delete
        
        // Invalidar cache
        Cache::forget("product_{$id}");
        Cache::forget('products.all');
        // Invalidar cache de productos filtrados
        $this->clearProductsCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado exitosamente'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}/restore",
     *     summary="Restaurar un producto eliminado lógicamente",
     *     description="Restaura un producto que fue eliminado mediante soft delete. El producto vuelve a estar disponible para consultas normales. Requiere autenticación. Solo funciona con productos que han sido eliminados lógicamente. Invalida automáticamente el cache.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto eliminado que se desea restaurar",
     *         @OA\Schema(type="integer", minimum=1),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto restaurado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto restaurado exitosamente"),
     *             @OA\Property(property="product", ref="#/components/schemas/ProductDetail")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado o no está eliminado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 1")
     *         )
     *     )
     * )
     */
    public function restore($id)
    {
        // Verificar primero si el producto existe (activo o eliminado)
        $product = Product::withTrashed()->find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
        
        // Verificar si el producto NO está eliminado
        if (!$product->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'El producto no está eliminado, no se puede restaurar'
            ], 400);
        }
        
        $product->restore();
        
        // Invalidar cache
        Cache::forget("product_{$id}");
        Cache::forget('products.all');
        // Invalidar cache de productos filtrados
        $this->clearProductsCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Producto restaurado exitosamente',
            'product' => $product
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}/force",
     *     summary="Eliminar permanentemente un producto",
     *     description="Elimina un producto de forma permanente de la base de datos. Esta acción es irreversible. Requiere autenticación. Solo funciona con productos que ya han sido eliminados lógicamente (soft deleted). Invalida automáticamente el cache.",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID único del producto eliminado lógicamente que se desea eliminar permanentemente",
     *         @OA\Schema(type="integer", minimum=1),
     *         example=1
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado permanentemente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto eliminado permanentemente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado o no está eliminado lógicamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No query results for model [App\\Models\\Product] 1")
     *         )
     *     )
     * )
     */
    public function forceDelete($id)
    {
        // Verificar primero si el producto existe
        $product = Product::withTrashed()->find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
        
        // Verificar si el producto NO está eliminado (no se puede forzar eliminación de producto activo)
        if (!$product->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar permanentemente un producto activo. Primero debe eliminarlo lógicamente.'
            ], 400);
        }
        
        $product->forceDelete(); // Eliminar permanentemente
        
        // Invalidar cache
        Cache::forget("product_{$id}");
        Cache::forget('products.all');
        // Invalidar cache de productos filtrados
        $this->clearProductsCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado permanentemente'
        ]);
    }

    /**
     * Limpiar cache de productos filtrados
     * Usa cache tagging si está disponible (Redis), sino fallback manual
     */
    private function clearProductsCache()
    {
        // Si tenemos Redis, podemos usar cache tagging
        if (config('cache.default') === 'redis') {
            try {
                Cache::tags(['products'])->flush();
                return;
            } catch (\Exception $e) {
                // Si falla el tagging, continuar con el método manual
            }
        }
        
        // Método manual para drivers sin soporte de tagging
        $commonFilters = [
            'products_filtered_default',
            'products_filtered_' . md5(''),
            'products_filtered_' . md5('page=1'),
            'products_filtered_' . md5('per_page=15'),
        ];
        
        foreach ($commonFilters as $key) {
            Cache::forget($key);
        }
    }
    
    /**
     * Convierte parámetros directos como ?name=valor a formato filter[campo]=valor
     * para mayor flexibilidad en la API
     */
    private function convertDirectParamsToFilters(Request $request)
    {
        $directParams = ['name', 'price', 'min_price', 'max_price', 'in_stock'];
        $currentFilters = $request->input('filter', []);
        
        foreach ($directParams as $param) {
            if ($request->has($param) && !isset($currentFilters[$param])) {
                $currentFilters[$param] = $request->input($param);
            }
        }
        
        if (!empty($currentFilters)) {
            $request->merge(['filter' => $currentFilters]);
        }
    }
}
