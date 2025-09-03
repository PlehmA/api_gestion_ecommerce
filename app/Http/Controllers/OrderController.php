<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

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
     *     summary="Obtener órdenes del usuario autenticado",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de órdenes",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Order")
     *         )
     *     )
     * )
     */
    public function index(Request $request, OrderService $orderService)
    {
        $orders = $orderService->getUserOrders($request->user()->id);
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
