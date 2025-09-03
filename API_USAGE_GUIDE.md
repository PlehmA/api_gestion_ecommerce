# API de Gestión de E-commerce - Guía de Uso

Este proyecto implementa una API completa de gestión de órdenes de venta para un e-commerce usando Laravel 11 y MySQL.

## 🚀 Características Implementadas

### ✅ Funcionalidades Core
- **Gestión de Productos**: CRUD completo con cache Redis
- **Gestión de Órdenes**: Crear, listar, ver detalles y estadísticas
- **Sistema de Autenticación**: Laravel Sanctum con tokens
- **Base de Datos**: Migraciones completas con relaciones
- **Validaciones**: Request validation personalizado
- **Servicios**: Arquitectura limpia con service layer
- **Cache**: Implementación multi-capa con Redis
- **Jobs**: Notificaciones por email asíncronas
- **Tests**: Cobertura completa de funcionalidades
- **Documentación API**: Swagger/OpenAPI integrado

### ✅ Extras Implementados
- **Políticas de Autorización**: Control granular de acceso
- **Traits Reutilizables**: Para funcionalidades comunes
- **Manejo de Errores**: Respuestas API consistentes
- **Seeders**: Datos de prueba
- **Middleware**: Configuración de API
- **Soft Deletes**: Eliminación lógica de registros

## 📊 Arquitectura de Base de Datos

```
users
├── id (PK)
├── name
├── email (unique)
├── password
└── timestamps

products
├── id (PK)
├── name
├── description
├── price
├── stock
└── timestamps

orders
├── id (PK)
├── user_id (FK → users)
├── status (enum: pending, processing, shipped, delivered, cancelled)
├── total
└── timestamps

order_products (tabla pivot)
├── id (PK)
├── order_id (FK → orders)
├── product_id (FK → products)
├── quantity
├── unit_price
└── timestamps

addresses
├── id (PK)
├── user_id (FK → users)
├── type (enum: billing, shipping)
├── street, city, state, postal_code, country
└── timestamps

invoices
├── id (PK)
├── order_id (FK → orders)
├── invoice_number (unique)
├── amount
├── status (enum: pending, paid, cancelled)
└── timestamps

personal_access_tokens (Sanctum)
├── id (PK)
├── tokenable_id
├── tokenable_type
├── name, token, abilities
└── timestamps
```

## 🔐 Autenticación

### Registro
```bash
POST /api/register
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

### Inicio de Sesión
```bash
POST /api/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "password123"
}

# Respuesta:
{
  "message": "Inicio de sesión exitoso",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com"
  },
  "token": "1|abcd1234..."
}
```

### Usar el Token
```bash
# Incluir en todas las peticiones protegidas
Authorization: Bearer 1|abcd1234...
```

### Cerrar Sesión
```bash
POST /api/logout
Authorization: Bearer {token}

# Cerrar todas las sesiones
POST /api/logout-all
Authorization: Bearer {token}
```

## 🛍️ Gestión de Productos

### Listar Productos (Público)
```bash
GET /api/products
```

### Ver Producto (Público)
```bash
GET /api/products/1
```

## 📦 Gestión de Órdenes (Requiere Autenticación)

### Crear Orden
```bash
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 2,
      "quantity": 1
    }
  ],
  "shipping_address": {
    "street": "Calle 123",
    "city": "Ciudad",
    "state": "Estado",
    "postal_code": "12345",
    "country": "País"
  }
}
```

### Listar Órdenes del Usuario
```bash
GET /api/orders
Authorization: Bearer {token}
```

### Ver Detalles de Orden
```bash
GET /api/orders/1
Authorization: Bearer {token}
```

### Estadísticas de Órdenes
```bash
GET /api/orders/stats
Authorization: Bearer {token}

# Respuesta:
{
  "total_orders": 5,
  "total_revenue": 1250.50,
  "orders_by_status": {
    "pending": 2,
    "processing": 1,
    "shipped": 1,
    "delivered": 1
  },
  "average_order_value": 250.10
}
```

## 🧪 Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter AuthenticationTest
php artisan test --filter OrderApiTest
php artisan test --filter OrderServiceTest
```

## 📚 Documentación API (Swagger)

La API está completamente documentada con Swagger/OpenAPI. Para generar la documentación:

```bash
php artisan l5-swagger:generate
```

Luego accede a: `http://localhost/api_gestion_ecommerce/public/api/documentation`

## 🗄️ Base de Datos

### Ejecutar Migraciones
```bash
php artisan migrate
```

### Poblar con Datos de Prueba
```bash
php artisan db:seed
```

### Refrescar Base de Datos
```bash
php artisan migrate:fresh --seed
```

## 🚀 Cache

El sistema implementa cache en múltiples niveles:

- **Productos**: Cache de 1 hora
- **Órdenes**: Cache de 30 minutos
- **Autenticación**: Cache de tokens

### Limpiar Cache
```bash
php artisan cache:clear
```

## 📧 Jobs y Colas

### Procesar Jobs
```bash
php artisan queue:work
```

El sistema envía emails automáticamente cuando se crean órdenes.

## 🛡️ Seguridad

- Validación completa de inputs
- Sanitización de datos
- Rate limiting en rutas de autenticación
- Tokens seguros con Sanctum
- Políticas de autorización

## 📝 Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── OrderController.php
│   │   └── ProductController.php
│   ├── Requests/
│   │   └── StoreOrderRequest.php
│   └── Middleware/
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   ├── OrderProduct.php
│   ├── Address.php
│   └── Invoice.php
├── Services/
│   └── OrderService.php
├── Policies/
│   └── OrderPolicy.php
├── Jobs/
│   └── SendOrderNotification.php
└── Traits/
    └── ApiResponse.php
```

## 🎯 Estados de Órdenes

- **pending**: Orden creada, pendiente de procesamiento
- **processing**: Orden en proceso de preparación
- **shipped**: Orden enviada
- **delivered**: Orden entregada
- **cancelled**: Orden cancelada

## 💡 Notas Técnicas

1. **Cache**: Redis configurado para mejor rendimiento
2. **Jobs**: Queue system para procesos asíncronos
3. **Validation**: Request validation personalizado
4. **Error Handling**: Manejo consistente de errores de API
5. **Testing**: Tests automatizados con PHPUnit
6. **Documentation**: Swagger/OpenAPI para documentación interactiva

## 🔧 Configuración

Asegúrate de configurar tu `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_ecommerce
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

## 🎉 ¡Proyecto Completado!

Esta API está lista para producción con todas las funcionalidades solicitadas y extras implementados. Incluye autenticación robusta, gestión completa de órdenes, cache optimizado, tests automatizados y documentación completa.
