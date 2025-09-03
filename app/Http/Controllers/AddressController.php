<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/addresses",
     *     summary="Obtener direcciones del usuario autenticado",
     *     description="Retorna todas las direcciones del usuario autenticado",
     *     tags={"Addresses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filtrar por tipo de dirección",
     *         @OA\Schema(type="string", enum={"delivery", "billing"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de direcciones del usuario",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Address")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Address::where('user_id', $request->user()->id);
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $addresses = $query->get();
        
        return response()->json($addresses);
    }

    /**
     * @OA\Post(
     *     path="/api/addresses",
     *     summary="Crear una nueva dirección",
     *     tags={"Addresses"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"street", "city", "state", "zip", "country", "type"},
     *             @OA\Property(property="street", type="string", example="123 Main Street"),
     *             @OA\Property(property="city", type="string", example="New York"),
     *             @OA\Property(property="state", type="string", example="NY"),
     *             @OA\Property(property="zip", type="string", example="10001"),
     *             @OA\Property(property="country", type="string", example="USA"),
     *             @OA\Property(property="type", type="string", enum={"delivery", "billing"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Dirección creada exitosamente")
     * )
     */
    public function store(StoreAddressRequest $request)
    {
        $address = Address::create([
            'user_id' => $request->user()->id,
            ...$request->validated()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Dirección creada exitosamente',
            'address' => $address
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/addresses/{id}",
     *     summary="Obtener una dirección específica",
     *     tags={"Addresses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200, 
     *         description="Detalles de la dirección",
     *         @OA\JsonContent(ref="#/components/schemas/Address")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dirección no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dirección no encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Sin permisos",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No tienes permisos para ver esta dirección")
     *         )
     *     )
     * )
     */
    public function show(Request $request, $id)
    {
        $address = Address::find($id);
        
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Dirección no encontrada'
            ], 404);
        }
        
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para ver esta dirección'
            ], 403);
        }
        
        return response()->json($address);
    }

    /**
     * @OA\Put(
     *     path="/api/addresses/{id}",
     *     summary="Actualizar una dirección",
     *     tags={"Addresses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dirección actualizada exitosamente"),
     *     @OA\Response(response=404, description="Dirección no encontrada"),
     *     @OA\Response(response=403, description="Sin permisos")
     * )
     */
    public function update(UpdateAddressRequest $request, $id)
    {
        $address = Address::find($id);
        
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Dirección no encontrada'
            ], 404);
        }
        
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para actualizar esta dirección'
            ], 403);
        }
        
        $address->update($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Dirección actualizada exitosamente',
            'address' => $address
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/addresses/{id}",
     *     summary="Eliminar una dirección",
     *     tags={"Addresses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Dirección eliminada exitosamente"),
     *     @OA\Response(response=404, description="Dirección no encontrada"),
     *     @OA\Response(response=403, description="Sin permisos")
     * )
     */
    public function destroy(Request $request, $id)
    {
        $address = Address::find($id);
        
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Dirección no encontrada'
            ], 404);
        }
        
        if ($address->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para eliminar esta dirección'
            ], 403);
        }
        
        $address->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Dirección eliminada exitosamente'
        ]);
    }
}
