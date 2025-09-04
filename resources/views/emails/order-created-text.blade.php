¡Gracias por tu compra!
Confirmación de Orden #{{ $order->id }}

Hola {{ $user->name }},

Tu orden ha sido creada exitosamente. A continuación encontrarás los detalles de tu compra:

INFORMACIÓN DE LA ORDEN
-----------------------
Número de Orden: #{{ $order->id }}
Fecha: {{ $order->created_at->format('d/m/Y H:i') }}
Estado: {{ ucfirst($order->status) }}

@if($address)
DIRECCIÓN DE ENTREGA
--------------------
{{ $address->street }}
{{ $address->city }}, {{ $address->state }}
{{ $address->zip }}, {{ $address->country }}

@endif
PRODUCTOS ORDENADOS
-------------------
@foreach($products as $product)
{{ $product->name }}
{{ $product->description }}
Cantidad: {{ $product->pivot->quantity }} | Precio: ${{ number_format($product->pivot->price, 2) }} | Subtotal: ${{ number_format($product->pivot->quantity * $product->pivot->price, 2) }}

@endforeach

TOTAL: ${{ number_format($total, 2) }}

Te mantendremos informado sobre el estado de tu orden. Si tienes alguna pregunta, no dudes en contactarnos.

---
© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
Este es un email automático, por favor no respondas a este mensaje.
