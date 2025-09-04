<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Jobs\SendOrderCreatedEmail;
use Illuminate\Console\Command;

class TestOrderEmail extends Command
{
    protected $signature = 'test:order-email {order_id?}';
    protected $description = 'Test sending order created email';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        
        if (!$orderId) {
            // Buscar la primera orden disponible
            $order = Order::with(['user', 'products', 'address'])->first();
            if (!$order) {
                $this->error('❌ No hay órdenes en la base de datos para probar');
                return;
            }
        } else {
            $order = Order::with(['user', 'products', 'address'])->find($orderId);
            if (!$order) {
                $this->error("❌ No se encontró la orden con ID: {$orderId}");
                return;
            }
        }

        $this->info("📧 Enviando email para la orden #{$order->id}...");
        $this->line("👤 Usuario: {$order->user->name} ({$order->user->email})");
        $this->line("💰 Total: \${$order->total}");
        $this->line("📦 Productos: {$order->products->count()}");
        
        // Despachar el job
        SendOrderCreatedEmail::dispatch($order);
        
        $this->info("✅ Job de email despachado exitosamente");
        $this->line("🔄 Verifica la cola con: php artisan queue:work");
        
        // Mostrar información adicional
        $this->newLine();
        $this->line("📋 Información del Job:");
        $this->line("   - Conexión de cola: " . config('queue.default'));
        $this->line("   - Driver de mail: " . config('mail.default'));
        $this->line("   - Host SMTP: " . config('mail.mailers.smtp.host'));
    }
}
