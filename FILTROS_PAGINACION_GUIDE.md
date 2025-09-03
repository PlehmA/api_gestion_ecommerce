# Guía de Uso - Filtros y Paginación con Spatie Query Builder

## API de Productos (/api/products)

### Ejemplos de uso con filtros y paginación:

### 1. Paginación básica
```bash
GET /api/products?page=1&per_page=10
```

### 2. Filtrar por nombre (búsqueda parcial)
```bash
GET /api/products?filter[name]=laptop
```

### 3. Filtrar por rango de precios
```bash
GET /api/products?filter[min_price]=100&filter[max_price]=1000
```

### 4. Solo productos en stock
```bash
GET /api/products?filter[in_stock]=true
```

### 5. Ordenamiento
```bash
# Ordenar por precio ascendente
GET /api/products?sort=price

# Ordenar por precio descendente
GET /api/products?sort=-price

# Ordenar por nombre
GET /api/products?sort=name

# Ordenar por fecha de creación (más recientes primero)
GET /api/products?sort=-created_at
```

### 6. Combinando múltiples filtros
```bash
GET /api/products?filter[name]=gaming&filter[min_price]=500&filter[max_price]=2000&filter[in_stock]=true&sort=-price&page=1&per_page=5
```

### 7. Ejemplo de respuesta paginada
```json
{
  "data": [
    {
      "id": 1,
      "name": "Laptop Gaming ASUS",
      "description": "Laptop para gaming con RTX 4060",
      "price": 125999.99,
      "stock": 15,
      "created_at": "2025-09-03T10:00:00.000000Z",
      "updated_at": "2025-09-03T10:00:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/products?page=1",
    "last": "http://localhost:8000/api/products?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/products?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 75
  }
}
```

---

## API de Órdenes (/api/orders) - Requiere Autenticación

### Ejemplos de uso con filtros y paginación:

### 1. Paginación básica
```bash
GET /api/orders?page=1&per_page=10
Authorization: Bearer {token}
```

### 2. Filtrar por estado
```bash
GET /api/orders?filter[status]=pending
Authorization: Bearer {token}
```

### 3. Filtrar por rango de fechas
```bash
GET /api/orders?filter[date_from]=2025-09-01&filter[date_to]=2025-09-03
Authorization: Bearer {token}
```

### 4. Filtrar por rango de totales
```bash
GET /api/orders?filter[min_total]=100&filter[max_total]=1000
Authorization: Bearer {token}
```

### 5. Ordenamiento
```bash
# Más recientes primero (por defecto)
GET /api/orders?sort=-created_at

# Más antiguas primero
GET /api/orders?sort=created_at

# Por total descendente
GET /api/orders?sort=-total

# Por estado
GET /api/orders?sort=status
```

### 6. Combinando múltiples filtros
```bash
GET /api/orders?filter[status]=delivered&filter[min_total]=500&filter[date_from]=2025-08-01&sort=-total&page=1&per_page=5
Authorization: Bearer {token}
```

### 7. Todos los estados disponibles
- `pending`: Pendiente
- `processing`: Procesando
- `shipped`: Enviado
- `delivered`: Entregado
- `cancelled`: Cancelado

---

## Funcionalidades de Spatie Query Builder

### Operadores disponibles:

1. **Filtros exactos**: `filter[campo]=valor`
2. **Filtros parciales**: `filter[campo]=texto` (para búsquedas de texto)
3. **Filtros por scope**: `filter[min_price]=100` (usa scopes del modelo)
4. **Ordenamiento**: `sort=campo` o `sort=-campo` (descendente)
5. **Paginación**: `page=1&per_page=15`

### Límites de seguridad:

- **Productos**: máximo 100 elementos por página
- **Órdenes**: máximo 50 elementos por página
- **Cache**: Respuestas cacheadas por 5 minutos (300 segundos)
- **Seguridad**: Solo puedes ver tus propias órdenes

---

## Ejemplos con cURL

### Productos sin autenticación
```bash
# Buscar productos gaming con precio entre 500 y 2000
curl -X GET "http://localhost:8000/api/products?filter[name]=gaming&filter[min_price]=500&filter[max_price]=2000&sort=-price&page=1&per_page=5" \
     -H "Accept: application/json"
```

### Órdenes con autenticación
```bash
# Obtener órdenes pendientes del último mes
curl -X GET "http://localhost:8000/api/orders?filter[status]=pending&filter[date_from]=2025-08-01&sort=-created_at" \
     -H "Accept: application/json" \
     -H "Authorization: Bearer {tu_token_aqui}"
```

---

## Performance y Cache

- Todas las consultas se cachean automáticamente
- El cache se invalida cuando cambian los parámetros de consulta
- Cache key único por usuario para órdenes
- Cache compartido para productos (son públicos)

---

## Notas de Implementación

1. **Seguridad**: Los filtros están pre-definidos y validados
2. **Rendimiento**: Uso de índices de base de datos recomendado
3. **Escalabilidad**: Paginación obligatoria para grandes datasets
4. **Flexibilidad**: Combinación libre de filtros y ordenamientos
