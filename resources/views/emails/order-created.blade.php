<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Orden</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4f46e5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        .order-details {
            background-color: white;
            padding: 15px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .product-item {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 0;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .total {
            font-size: 1.2em;
            font-weight: bold;
            color: #059669;
            text-align: right;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
        }
        .footer {
            background-color: #374151;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 0 0 8px 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875em;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-confirmed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-processing {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Gracias por tu compra!</h1>
        <p>Confirmación de Orden #{{ $order->id }}</p>
    </div>

    <div class="content">
        <h2>Hola {{ $user->name }},</h2>
        <p>Tu orden ha sido creada exitosamente. A continuación encontrarás los detalles de tu compra:</p>

        <div class="order-details">
            <h3>Información de la Orden</h3>
            <p><strong>Número de Orden:</strong> #{{ $order->id }}</p>
            <p><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Estado:</strong> 
                <span class="status-badge status-{{ $order->status }}">
                    {{ ucfirst($order->status) }}
                </span>
            </p>
        </div>

        @if($address)
        <div class="order-details">
            <h3>Dirección de Entrega</h3>
            <p>
                {{ $address->street }}<br>
                {{ $address->city }}, {{ $address->state }}<br>
                {{ $address->zip }}, {{ $address->country }}
            </p>
        </div>
        @endif

        <div class="order-details">
            <h3>Productos Ordenados</h3>
            @foreach($products as $product)
                <div class="product-item">
                    <strong>{{ $product->name }}</strong><br>
                    <small>{{ $product->description }}</small><br>
                    <span>Cantidad: {{ $product->pivot->quantity }}</span> | 
                    <span>Precio: ${{ number_format($product->pivot->price, 2) }}</span> | 
                    <span>Subtotal: ${{ number_format($product->pivot->quantity * $product->pivot->price, 2) }}</span>
                </div>
            @endforeach
            
            <div class="total">
                Total: ${{ number_format($total, 2) }}
            </div>
        </div>

        <p>Te mantendremos informado sobre el estado de tu orden. Si tienes alguna pregunta, no dudes en contactarnos.</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        <p>Este es un email automático, por favor no respondas a este mensaje.</p>
    </div>
</body>
</html>
