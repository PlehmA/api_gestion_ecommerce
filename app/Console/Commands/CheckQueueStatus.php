<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;

class CheckQueueStatus extends Command
{
    protected $signature = 'queue:status';
    protected $description = 'Check queue status and configuration';

    public function handle()
    {
        $this->info('🔍 Verificando estado de la cola...');
        
        // Configuración
        $this->line('📋 Configuración:');
        $this->line('   - Driver de cola: ' . config('queue.default'));
        $this->line('   - Conexión Redis: ' . config('database.redis.default.host') . ':' . config('database.redis.default.port'));
        
        // Verificar Redis
        try {
            $redis = Redis::connection();
            $this->info('✅ Conexión Redis: OK');
            
            // Obtener estadísticas de Redis
            $queues = ['default'];
            foreach ($queues as $queue) {
                $size = $redis->llen("queues:{$queue}");
                $this->line("   - Cola '{$queue}': {$size} jobs pendientes");
            }
            
            // Verificar jobs en proceso
            $processing = $redis->llen('queues:default:reserved');
            $this->line("   - Jobs en proceso: {$processing}");
            
        } catch (\Exception $e) {
            $this->error('❌ Error de Redis: ' . $e->getMessage());
        }
        
        // Configuración de email
        $this->newLine();
        $this->line('📧 Configuración de Email:');
        $this->line('   - Driver: ' . config('mail.default'));
        $this->line('   - Host: ' . config('mail.mailers.smtp.host'));
        $this->line('   - Puerto: ' . config('mail.mailers.smtp.port'));
        $this->line('   - Usuario: ' . config('mail.mailers.smtp.username'));
        $this->line('   - Desde: ' . config('mail.from.address'));
    }
}
