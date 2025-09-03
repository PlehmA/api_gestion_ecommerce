<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class TestRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la conexión y funcionalidades de Redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Probando Redis...');
        
        try {
            // Probar conexión básica
            $this->info('1️⃣ Probando conexión Redis...');
            Redis::ping();
            $this->info('✅ Conexión Redis exitosa');
            
            // Probar cache básico
            $this->info('2️⃣ Probando cache básico...');
            Cache::put('test_key', 'test_value', 60);
            $value = Cache::get('test_key');
            
            if ($value === 'test_value') {
                $this->info('✅ Cache básico funcionando');
            } else {
                $this->error('❌ Cache básico fallando');
            }
            
            // Probar cache tagging
            $this->info('3️⃣ Probando cache tagging...');
            try {
                Cache::tags(['test_tag'])->put('tagged_key', 'tagged_value', 60);
                $taggedValue = Cache::tags(['test_tag'])->get('tagged_key');
                
                if ($taggedValue === 'tagged_value') {
                    $this->info('✅ Cache tagging funcionando');
                    
                    // Probar flush de tags
                    Cache::tags(['test_tag'])->flush();
                    $afterFlush = Cache::tags(['test_tag'])->get('tagged_key');
                    
                    if ($afterFlush === null) {
                        $this->info('✅ Cache tag flush funcionando');
                    } else {
                        $this->error('❌ Cache tag flush fallando');
                    }
                } else {
                    $this->error('❌ Cache tagging fallando');
                }
            } catch (\Exception $e) {
                $this->error('❌ Cache tagging no soportado: ' . $e->getMessage());
            }
            
            // Información del sistema
            $this->info('📊 Información del sistema:');
            $this->line('Cache Driver: ' . config('cache.default'));
            $this->line('Redis Host: ' . config('database.redis.default.host'));
            $this->line('Redis Port: ' . config('database.redis.default.port'));
            
            // Limpiar
            Cache::forget('test_key');
            
            $this->info('🎉 Pruebas de Redis completadas');
            
        } catch (\Exception $e) {
            $this->error('❌ Error conectando a Redis: ' . $e->getMessage());
            $this->line('');
            $this->line('💡 Posibles soluciones:');
            $this->line('1. Instalar Redis: https://redis.io/download');
            $this->line('2. Usar Docker: docker run -d -p 6379:6379 redis:alpine');
            $this->line('3. Verificar que Redis esté ejecutándose en puerto 6379');
            
            return 1;
        }
        
        return 0;
    }
}
