<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * @OA\Info(
 *     title="API Gestión E-commerce",
 *     version="1.0.0",
 *     description="API para gestión de órdenes de venta de un e-commerce"
 * )
 */

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/orders",
     *     summary="Obtener órdenes del usuario autenticado con filtros y paginación",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         description="Filtrar por estado",
     *         @OA\Schema(type="string", enum={"pending", "processing", "shipped", "delivered", "cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="filter[min_total]",
     *         in="query",
     *         description="Total mínimo",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="filter[max_total]",
     *         in="query",
     *         description="Total máximo",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="filter[date_from]",
     *         in="query",
     *         description="Fecha desde",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="filter[date_to]",
     *         in="query",
     *         description="Fecha hasta",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Ordenar por campo (ej: -created_at, total, status)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número de página",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Elementos por página (máximo 50)",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de órdenes",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        // Crear cache key único basado en usuario y parámetros de consulta
        $cacheKey = 'orders_user_' . $request->user()->id . '_' . md5($request->getQueryString() ?: 'default');
        
        $orders = Cache::remember($cacheKey, 300, function () use ($request) {
            return QueryBuilder::for(Order::class)
                ->where('user_id', $request->user()->id)
                ->with(['products', 'address', 'invoice'])
                ->allowedFilters([
                    AllowedFilter::exact('status'),
                    AllowedFilter::scope('min_total'),
                    AllowedFilter::scope('max_total'),
                    AllowedFilter::scope('date_from'),
                    AllowedFilter::scope('date_to'),
                ])
                ->allowedSorts([
                    'created_at',
                    'total', 
                    'status',
                    'updated_at'
                ])
                ->defaultSort('-created_at')
                ->paginate($request->get('per_page', 15))
                ->withQueryString();
        });
        
        return response()->json($orders);
    }

    /**
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Crear una nueva orden",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id","address_id","products"},
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="address_id", type="integer"),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Orden creada",
     *         @OA\JsonContent(ref="#/components/schemas/Order")
     *     ),
     *     @OA\Response(response=422, description="Datos inválidos")
     * )
     */
    public function store(StoreOrderRequest $request, OrderService $orderService)
    {
        $order = $orderService->createOrder($request->validated());
        return response()->json($order, 201);
    }

        /**
         * @OA\Get(
         *     path="/api/orders/{id}",
         *     summary="Obtener el detalle de una orden",
         *     tags={"Orders"},
         *     security={{"sanctum":{}}},
         *     @OA\Parameter(
         *         name="id",
         *         in="path",
         *         required=true,
         *         @OA\Schema(type="integer")
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Detalle de la orden",
         *         @OA\JsonContent(ref="#/components/schemas/Order")
         *     ),
         *     @OA\Response(response=404, description="Orden no encontrada")
         * )
         */
    public function show($id)
    {
        $order = Cache::remember("order_{$id}", 60, function () use ($id) {
            return Order::with(['user', 'address', 'invoice', 'products'])->findOrFail($id);
        });
        Gate::authorize('view', $order);
        return response()->json($order);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/stats",
     *     summary="Obtener estadísticas de órdenes",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Estadísticas de órdenes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="total_orders", type="integer"),
     *             @OA\Property(property="pending_orders", type="integer"),
     *             @OA\Property(property="completed_orders", type="integer"),
     *             @OA\Property(property="total_revenue", type="number", format="float")
     *         )
     *     )
     * )
     */
    public function stats(OrderService $orderService)
    {
        $stats = $orderService->getOrderStats();
        return response()->json($stats);
    }
}
