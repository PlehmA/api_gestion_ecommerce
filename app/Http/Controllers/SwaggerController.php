<?php

/**
 * @OA\Info(
 *     title="API Gestión E-commerce",
 *     version="1.0",
 *     description="API para gestión de órdenes de venta de un e-commerce.",
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
 *     name="Orders",
 *     description="Operaciones sobre órdenes de venta"
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
