# API Gestión E-commerce

## Documentación Swagger de la API

Se ha generado una documentación completa de la API utilizando Swagger/OpenAPI. La documentación incluye todos los endpoints, parámetros, ejemplos de solicitud y respuesta, y esquemas de modelos.

### Acceder a la Documentación Swagger

1. Inicia el servidor de desarrollo:
   ```bash
   php artisan serve
   ```

2. Abre la documentación Swagger en tu navegador:
   ```
   http://localhost:8000/api/documentation
   ```

### Endpoints de Productos Documentados

#### Listado y Consulta
- `GET /api/products` - Lista de productos con filtros avanzados y paginación
- `GET /api/products/{id}` - Detalle de un producto específico

#### CRUD con SoftDelete
- `POST /api/products` - Crear un nuevo producto
- `PUT /api/products/{id}` - Actualizar un producto existente
- `DELETE /api/products/{id}` - Eliminar un producto (soft delete)
- `POST /api/products/{id}/restore` - Restaurar un producto eliminado
- `DELETE /api/products/{id}/force` - Eliminar permanentemente un producto

### Cómo Regenerar la Documentación Swagger

Si realizas cambios en las anotaciones Swagger, puedes regenerar la documentación con el siguiente comando:

```bash
php artisan l5-swagger:generate
```

### Esquemas de Modelos

La documentación incluye esquemas completos para todos los modelos:
- `Product` - Estructura completa del modelo producto
- `ProductStore` - Estructura para crear un producto
- `ProductUpdate` - Estructura para actualizar un producto
- `ProductPaginated` - Estructura de respuesta paginada

### Seguridad

La documentación incluye información sobre los requerimientos de autenticación para cada endpoint:
- Endpoints públicos: no requieren autenticación
- Endpoints protegidos: requieren token Bearer (Sanctum)

### Filtrado Avanzado de Productos

La documentación detalla todas las opciones de filtrado disponibles:
- `filter[name]` - Filtrar por nombre (búsqueda parcial)
- `filter[price]` - Filtrar por precio exacto
- `filter[min_price]` - Filtrar por precio mínimo
- `filter[max_price]` - Filtrar por precio máximo
- `filter[in_stock]` - Filtrar productos en stock (1) o sin stock (0)
- `sort` - Ordenar por nombre, precio, stock, fecha (usar - para orden descendente)
- `page` - Número de página para paginación
- `per_page` - Elementos por página (máximo 100)
