<?php

namespace App\Http\Controllers;

/**
 * @OA\Schema(
 *     schema="ProductDetail",
 *     title="Product Detail",
 *     description="Modelo detallado de producto del e-commerce",
 *     required={"id", "name", "description", "price", "stock"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID único del producto",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Nombre del producto",
 *         example="iPhone 15 Pro"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Descripción detallada del producto",
 *         example="Último modelo de iPhone con chip A17 Pro"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         minimum=0.01,
 *         description="Precio del producto en USD",
 *         example=1199.99
 *     ),
 *     @OA\Property(
 *         property="stock",
 *         type="integer",
 *         minimum=0,
 *         description="Cantidad disponible en inventario",
 *         example=50
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de creación",
 *         example="2024-01-01T00:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Fecha y hora de última actualización",
 *         example="2024-01-01T12:30:45.000000Z"
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Fecha y hora de eliminación lógica (null si está activo)",
 *         example=null
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ProductStore",
 *     title="Product Store Request",
 *     description="Datos requeridos para crear un nuevo producto",
 *     required={"name", "description", "price", "stock"},
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Nombre único del producto",
 *         example="MacBook Pro M3"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Descripción detallada del producto",
 *         example="Laptop profesional con chip M3 y pantalla Retina"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         minimum=0.01,
 *         description="Precio del producto",
 *         example=2499.99
 *     ),
 *     @OA\Property(
 *         property="stock",
 *         type="integer",
 *         minimum=0,
 *         description="Cantidad inicial en inventario",
 *         example=25
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ProductUpdate",
 *     title="Product Update Request",
 *     description="Datos para actualizar un producto existente",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         description="Nuevo nombre del producto (opcional)",
 *         example="MacBook Pro M3 Actualizado"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Nueva descripción del producto (opcional)",
 *         example="Laptop profesional actualizada con mejor rendimiento"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         minimum=0.01,
 *         description="Nuevo precio del producto (opcional)",
 *         example=2299.99
 *     ),
 *     @OA\Property(
 *         property="stock",
 *         type="integer",
 *         minimum=0,
 *         description="Nueva cantidad en inventario (opcional)",
 *         example=15
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ProductPaginated",
 *     title="Products Paginated Response",
 *     description="Respuesta paginada de productos con filtros",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         description="Lista de productos",
 *         @OA\Items(ref="#/components/schemas/ProductDetail")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Enlaces de paginación",
 *         @OA\Property(property="first", type="string", example="http://localhost:8000/api/products?page=1"),
 *         @OA\Property(property="last", type="string", example="http://localhost:8000/api/products?page=10"),
 *         @OA\Property(property="prev", type="string", nullable=true, example=null),
 *         @OA\Property(property="next", type="string", example="http://localhost:8000/api/products?page=2")
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Metadatos de paginación",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=10),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="total", type="integer", example=150)
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ValidationError",
 *     title="Validation Error",
 *     description="Error de validación con detalles específicos por campo",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         example="The given data was invalid."
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="Errores de validación por campo",
 *         @OA\Property(
 *             property="name",
 *             type="array",
 *             @OA\Items(type="string", example="The name field is required.")
 *         ),
 *         @OA\Property(
 *             property="price",
 *             type="array",
 *             @OA\Items(type="string", example="The price must be at least 0.01.")
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="ApiResponse",
 *     title="API Response",
 *     description="Respuesta estándar de la API",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="Mensaje descriptivo de la operación",
 *         example="Producto creado exitosamente"
 *     )
 * )
 */
class SwaggerSchemasController extends Controller
{
    // Este controlador solo existe para contener los schemas de Swagger
}
