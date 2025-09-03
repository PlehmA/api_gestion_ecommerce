# API de Gestión E-commerce - CRUD de Productos con SoftDelete

## Funcionalidades Implementadas

### 1. CRUD Completo de Productos

#### Crear Producto
- **Endpoint**: `POST /api/products`
- **Autenticación**: Requerida
- **Validaciones**: 
  - name: requerido, único (ignora eliminados), máx 255 caracteres
  - description: requerida
  - price: requerido, numérico, mínimo 0.01
  - stock: requerido, entero, mínimo 0

**Ejemplo de solicitud:**
```json
{
    "name": "Producto Nuevo",
    "description": "Descripción del producto",
    "price": 299.99,
    "stock": 50
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Producto creado exitosamente",
    "product": {
        "id": 1,
        "name": "Producto Nuevo",
        "description": "Descripción del producto",
        "price": 299.99,
        "stock": 50,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z",
        "deleted_at": null
    }
}
```

#### Actualizar Producto
- **Endpoint**: `PUT /api/products/{id}`
- **Autenticación**: Requerida
- **Validaciones**: Mismas que crear, pero name único excepto para el mismo producto

**Ejemplo de solicitud:**
```json
{
    "name": "Producto Actualizado",
    "description": "Nueva descripción",
    "price": 399.99,
    "stock": 25
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Producto actualizado exitosamente",
    "product": {
        "id": 1,
        "name": "Producto Actualizado",
        "description": "Nueva descripción",
        "price": 399.99,
        "stock": 25,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:01.000000Z",
        "deleted_at": null
    }
}
```

#### Eliminar Producto (Soft Delete)
- **Endpoint**: `DELETE /api/products/{id}`
- **Autenticación**: Requerida
- **Comportamiento**: Eliminación lógica (soft delete)

**Respuesta exitosa (200):**
```json
{
    "message": "Producto eliminado exitosamente"
}
```

### 2. Funcionalidades Especiales de SoftDelete

#### Restaurar Producto Eliminado
- **Endpoint**: `POST /api/products/{id}/restore`
- **Autenticación**: Requerida
- **Comportamiento**: Restaura un producto eliminado lógicamente

**Respuesta exitosa (200):**
```json
{
    "message": "Producto restaurado exitosamente",
    "product": {
        "id": 1,
        "name": "Producto Restaurado",
        "description": "Descripción del producto",
        "price": 299.99,
        "stock": 50,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:02.000000Z",
        "deleted_at": null
    }
}
```

#### Eliminar Permanentemente
- **Endpoint**: `DELETE /api/products/{id}/force`
- **Autenticación**: Requerida
- **Comportamiento**: Eliminación permanente de la base de datos

**Respuesta exitosa (200):**
```json
{
    "message": "Producto eliminado permanentemente"
}
```

### 3. Listado con Filtros (Existente)

#### Obtener Productos con Filtros y Paginación
- **Endpoint**: `GET /api/products`
- **Autenticación**: No requerida
- **Filtros disponibles**:
  - `filter[name]`: Buscar por nombre
  - `filter[min_price]`: Precio mínimo
  - `filter[max_price]`: Precio máximo
  - `filter[in_stock]`: Productos en stock
  - `sort`: Ordenamiento (name, price, stock, created_at)
  - `page`: Número de página
  - `per_page`: Productos por página (máx 100)

**Ejemplo con filtros:**
```
GET /api/products?filter[name]=laptop&filter[min_price]=500&filter[max_price]=2000&filter[in_stock]=1&sort=price&page=1&per_page=10
```

#### Ver Producto Individual
- **Endpoint**: `GET /api/products/{id}`
- **Autenticación**: No requerida
- **Cache**: 10 minutos

### 4. Características Técnicas

#### Validación Inteligente
- **Nombres únicos**: La validación de nombre único ignora productos eliminados (soft delete)
- **Reutilización**: Se pueden crear productos con nombres de productos eliminados
- **Validación condicional**: En actualizaciones, se ignora el propio producto para validación unique

#### Cache Automático
- **Invalidación inteligente**: Todas las operaciones CRUD invalidan automáticamente:
  - Cache general de productos
  - Cache del producto específico
  - Cache por tags (productos)
- **Cache selectivo**: Solo las consultas GET utilizan cache

#### Seguridad
- **Autenticación**: Todas las operaciones de escritura requieren autenticación Sanctum
- **Autorización**: Solo usuarios autenticados pueden crear, actualizar o eliminar
- **Consultas públicas**: Los listados y visualización individual son públicos

### 5. Ejemplos de Uso Completo

#### Flujo Completo: Crear → Actualizar → Eliminar → Restaurar
```bash
# 1. Crear producto
curl -X POST http://localhost:8000/api/products \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "iPhone 15",
    "description": "Último modelo de iPhone",
    "price": 999.99,
    "stock": 100
  }'

# 2. Actualizar producto
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "iPhone 15 Pro",
    "price": 1199.99,
    "stock": 75
  }'

# 3. Eliminar producto (soft delete)
curl -X DELETE http://localhost:8000/api/products/1 \
  -H "Authorization: Bearer {token}"

# 4. Restaurar producto
curl -X POST http://localhost:8000/api/products/1/restore \
  -H "Authorization: Bearer {token}"

# 5. Eliminar permanentemente
curl -X DELETE http://localhost:8000/api/products/1/force \
  -H "Authorization: Bearer {token}"
```

#### Trabajar con Productos Eliminados
```php
// En el modelo o controlador
$activeProducts = Product::all(); // Solo productos activos
$trashedProducts = Product::onlyTrashed()->get(); // Solo eliminados
$allProducts = Product::withTrashed()->get(); // Todos (activos + eliminados)
```

### 6. Códigos de Estado HTTP

- **200**: Operación exitosa (actualizar, eliminar, restaurar, obtener)
- **201**: Producto creado exitosamente
- **401**: No autenticado (para operaciones protegidas)
- **404**: Producto no encontrado
- **422**: Error de validación (campos requeridos, duplicados, etc.)

### 7. Testing

Se han implementado 17 tests que cubren:
- ✅ Creación de productos con validaciones
- ✅ Actualización con validación unique inteligente
- ✅ Eliminación lógica (soft delete)
- ✅ Restauración de productos eliminados
- ✅ Eliminación permanente
- ✅ Validación de autenticación
- ✅ Invalidación de cache
- ✅ Validación de nombres únicos ignorando eliminados

### 8. Swagger/OpenAPI

Toda la API está documentada con anotaciones Swagger:
- Esquemas de request/response
- Códigos de estado
- Ejemplos de uso
- Validaciones requeridas

Para acceder a la documentación: `http://localhost:8000/api/documentation`

### 9. Base de Datos

#### Migración de SoftDelete
```sql
ALTER TABLE products ADD COLUMN deleted_at TIMESTAMP NULL;
```

#### Modelo Product
- Trait `SoftDeletes` habilitado
- Scopes personalizados para filtros
- Casting automático de tipos
- Validaciones en Form Requests

### 10. Arquitectura del Código

```
app/
├── Http/
│   ├── Controllers/
│   │   └── ProductController.php    # CRUD completo + SoftDelete
│   └── Requests/
│       ├── StoreProductRequest.php  # Validaciones para crear
│       └── UpdateProductRequest.php # Validaciones para actualizar
├── Models/
│   └── Product.php                 # Modelo con SoftDeletes + Scopes
└── ...

routes/api.php                      # Rutas públicas y protegidas
tests/Feature/ProductCrudTest.php    # 17 tests comprehensivos
```

Esta implementación proporciona un CRUD completo y robusto con SoftDelete para productos, manteniendo la compatibilidad con el sistema de filtros y paginación existente.
