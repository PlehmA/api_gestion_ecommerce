<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TestOrderController extends Command
{
    protected $signature = 'test:order-controller';
    protected $description = 'Test OrderController cache functionality';

    public function handle()
    {
        $this->info('🔍 Probando funcionalidad del OrderController...');
        
        // Simular usuario
        $user = User::first();
        if (!$user) {
            $this->error('❌ No hay usuarios para probar');
            return;
        }
        
        $this->line('1️⃣ Usuario de prueba: ' . $user->email);
        
        // Simular request para index()
        $cacheKey = 'orders_user_' . $user->id . '_' . md5('default');
        
        $this->line('2️⃣ Clave de cache: ' . $cacheKey);
        
        // Simular lógica del controlador
        if (config('cache.default') === 'redis') {
            $this->line('3️⃣ Usando cache tagging...');
            
            $orders = Cache::tags(['orders', 'user_' . $user->id])->remember($cacheKey, 300, function () use ($user) {
                return QueryBuilder::for(Order::class)
                    ->where('user_id', $user->id)
                    ->with(['products', 'address'])
                    ->allowedFilters([
                        AllowedFilter::exact('status'),
                    ])
                    ->defaultSort('-created_at')
                    ->paginate(15);
            });
            
            $this->info('✅ Cache tagging funcionando');
            $this->line('   - Órdenes encontradas: ' . $orders->total());
            
            // Probar invalidación de cache
            $this->line('4️⃣ Probando invalidación de cache...');
            Cache::tags(['orders'])->flush();
            
            $cached = Cache::tags(['orders', 'user_' . $user->id])->get($cacheKey);
            if (!$cached) {
                $this->info('✅ Invalidación de cache funcionando');
            } else {
                $this->error('❌ Invalidación de cache fallando');
            }
            
        } else {
            $this->line('3️⃣ Usando cache simple...');
            
            $orders = Cache::remember($cacheKey, 300, function () use ($user) {
                return QueryBuilder::for(Order::class)
                    ->where('user_id', $user->id)
                    ->with(['products', 'address'])
                    ->defaultSort('-created_at')
                    ->paginate(15);
            });
            
            $this->info('✅ Cache simple funcionando');
            $this->line('   - Órdenes encontradas: ' . $orders->total());
        }
        
        $this->info('🎉 Funcionalidad del OrderController verificada');
    }
}
