<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Mail\OrderCreatedMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestDirectEmail extends Command
{
    protected $signature = 'test:direct-email {order_id?}';
    protected $description = 'Test sending email directly (no queue)';

    public function handle()
    {
        $orderId = $this->argument('order_id') ?: 1;
        
        $order = Order::with(['user', 'products', 'address'])->find($orderId);
        if (!$order) {
            $this->error("❌ No se encontró la orden con ID: {$orderId}");
            return;
        }

        $this->info("📧 Enviando email directo para la orden #{$order->id}...");
        $this->line("👤 Usuario: {$order->user->name} ({$order->user->email})");
        
        try {
            // Enviar email directamente (sin cola)
            Mail::to($order->user->email)->send(new OrderCreatedMail($order));
            
            $this->info("✅ Email enviado exitosamente a {$order->user->email}");
            $this->line("📧 Revisa tu bandeja de entrada en Mailtrap");
            
        } catch (\Exception $e) {
            $this->error("❌ Error enviando email: " . $e->getMessage());
            $this->line("🔍 Detalles: " . $e->getTraceAsString());
        }
    }
}
