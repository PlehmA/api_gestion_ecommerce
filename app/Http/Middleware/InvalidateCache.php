<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class InvalidateCache
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Invalidar caché específico basado en la ruta y método
        $this->invalidateRelevantCache($request);

        return $response;
    }

    private function invalidateRelevantCache(Request $request)
    {
        $method = $request->method();
        $path = $request->path();

        // Invalidar caché cuando se crean, actualizan o eliminan recursos
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (str_contains($path, 'orders')) {
                // Invalidar caché relacionado con órdenes
                Cache::forget('order_stats');
                // Invalidar caché de órdenes de usuarios (esto se hace en el servicio)
            }
            
            if (str_contains($path, 'products')) {
                // Invalidar caché de productos
                Cache::forget('products.all');
            }
        }
    }
}
