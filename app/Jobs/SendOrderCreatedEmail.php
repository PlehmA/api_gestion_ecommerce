<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendOrderCreatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Cargar las relaciones necesarias si no están cargadas
            $this->order->load(['user', 'products', 'address']);
            
            // Enviar el email
            Mail::to($this->order->user->email)->send(new OrderCreatedMail($this->order));
            
            Log::info('Email de orden enviado exitosamente', [
                'order_id' => $this->order->id,
                'user_email' => $this->order->user->email
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error enviando email de orden', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage()
            ]);
            
            // Re-lanzar la excepción para que el job falle y se reintente
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de envío de email falló permanentemente', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}
