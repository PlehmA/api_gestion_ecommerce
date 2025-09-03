# 🔐 **Guía de Uso: Sistema de Autenticación**

## **Problema Resuelto: "Route [login] not defined"**

El error se solucionó configurando el manejo de excepciones para APIs en `bootstrap/app.php`. Ahora las rutas protegidas devuelven respuestas JSON apropiadas en lugar de intentar redirigir.

## **✅ Flujo Completo de Autenticación**

### **1. 📝 Registrar Usuario**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Respuesta:**
```json
{
  "message": "Usuario registrado exitosamente",
  "user": {
    "id": 1,
    "name": "Test User",
    "email": "test@example.com"
  },
  "token": "1|abc123def456..."
}
```

### **2. 🔑 Iniciar Sesión**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### **3. 👤 Acceder a Rutas Protegidas**
```bash
# Obtener datos del usuario
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer 1|abc123def456..."

# Crear una orden
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 1|abc123def456..." \
  -d '{
    "user_id": 1,
    "address_id": 1,
    "products": [{"id": 1, "quantity": 2}]
  }'
```

### **4. 🚪 Cerrar Sesión (CORREGIDO)**
```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer 1|abc123def456..."
```

**Respuesta (200):**
```json
{
  "message": "Sesión cerrada exitosamente"
}
```

### **5. �🚪 Cerrar TODAS las Sesiones (CORREGIDO)**
```bash
curl -X POST http://localhost:8000/api/logout-all \
  -H "Authorization: Bearer 1|abc123def456..."
```

**Respuesta (200):**
```json
{
  "message": "Todas las sesiones cerradas exitosamente"
}
```

### **6. �🚫 Acceso Sin Autenticación (CORREGIDO)**
```bash
# Intentar acceder sin token
curl -X GET http://localhost:8000/api/user
```

**Respuesta (401):**
```json
{
  "message": "No autenticado. Proporcione un token válido."
}
```

## **🛠️ Cambios Realizados para Corregir "Route [login] not defined"**

### **1. En `bootstrap/app.php`:**
```php
->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (AuthenticationException $e, $request) {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json([
                'message' => 'No autenticado. Proporcione un token válido.',
                'error' => 'Unauthenticated'
            ], 401);
        }
    });
})
```

### **2. En `routes/web.php`:**
```php
// Ruta temporal para evitar el error "Route [login] not defined"
Route::get('/login', function () {
    return response()->json(['message' => 'Esta es una API. Use POST /api/login para autenticarse.'], 404);
})->name('login');
```

### **3. Middleware mejorado:**
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(prepend: [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);
    
    $middleware->alias([
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
    ]);
})
```

Esto asegura que:
- ✅ Las APIs devuelven respuestas JSON en lugar de redirecciones
- ✅ El logout funciona correctamente
- ✅ Los errores de autenticación son claros y útiles
- ✅ No hay más errores de "Route [login] not defined"

## **🧪 Usuario de Prueba**
- **Email:** `demo@example.com`
- **Password:** `password123`

**¡El logout ya funciona perfectamente! 🎉**
