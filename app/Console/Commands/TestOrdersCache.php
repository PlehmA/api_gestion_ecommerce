<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class TestOrdersCache extends Command
{
    protected $signature = 'test:orders-cache';
    protected $description = 'Test Redis cache with orders and tagging';

    public function handle()
    {
        $this->info('🔍 Probando Redis cache con órdenes...');
        
        // 1. Verificar driver de cache
        $this->line('1️⃣ Driver de cache: ' . config('cache.default'));
        
        // 2. Contar órdenes en DB
        $ordersCount = Order::count();
        $this->line('2️⃣ Órdenes en DB: ' . $ordersCount);
        
        if ($ordersCount === 0) {
            $this->warn('⚠️ No hay órdenes para probar');
            return;
        }
        
        // 3. Probar cache básico
        $key = 'test_orders_' . time();
        $firstOrder = Order::first();
        
        Cache::put($key, $firstOrder, 60);
        $cached = Cache::get($key);
        
        if ($cached && $cached->id === $firstOrder->id) {
            $this->info('✅ Cache básico funcionando');
        } else {
            $this->error('❌ Cache básico fallando');
            return;
        }
        
        // 4. Probar cache tagging (solo si es Redis)
        if (config('cache.default') === 'redis') {
            $taggedKey = 'tagged_orders_' . time();
            
            Cache::tags(['orders', 'test'])->put($taggedKey, $firstOrder, 60);
            $taggedCached = Cache::tags(['orders', 'test'])->get($taggedKey);
            
            if ($taggedCached && $taggedCached->id === $firstOrder->id) {
                $this->info('✅ Cache tagging funcionando');
                
                // 5. Probar flush por tags
                Cache::tags(['test'])->flush();
                $afterFlush = Cache::tags(['orders', 'test'])->get($taggedKey);
                
                if (!$afterFlush) {
                    $this->info('✅ Cache tag flush funcionando');
                } else {
                    $this->error('❌ Cache tag flush no funcionando');
                }
            } else {
                $this->error('❌ Cache tagging fallando');
            }
        } else {
            $this->warn('⚠️ Cache tagging no disponible (no es Redis)');
        }
        
        // Limpiar cache de prueba
        Cache::forget($key);
        
        $this->info('🎉 Pruebas de cache completadas');
    }
}
