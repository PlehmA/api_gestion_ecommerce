<?php

/**
 * @OA\Info(
 *     title="API Gestión E-commerce",
 *     version="2.0",
 *     description="API para gestión de órdenes de venta y productos de un e-commerce. Incluye funcionalidades CRUD completas con SoftDelete para productos y filtrado avanzado con Spatie Query Builder.",
 *     @OA\Contact(
 *         email="admin@ecommerce.com",
 *         name="API Support"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="Operaciones de autenticación y autorización"
 * )
 * 
 * @OA\Tag(
 *     name="Products",
 *     description="Operaciones CRUD sobre productos con SoftDelete. Incluye creación, actualización, eliminación lógica, restauración y eliminación permanente. También soporta filtrado avanzado y paginación."
 * )
 * 
 * @OA\Tag(
 *     name="Orders",
 *     description="Operaciones sobre órdenes de venta con filtrado por usuario"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum token authentication"
 * )
 */

namespace App\Http\Controllers;

class SwaggerController extends Controller
{
    // Este controlador solo existe para contener las anotaciones base de Swagger
}
