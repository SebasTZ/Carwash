<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .container {
            width: 100%;
            max-width: 280px; /* Ticket estándar para impresoras térmicas */
            margin: 0 auto;
            padding: 10px;
        }
        .text-center {
            text-align: center;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .bold {
            font-weight: bold;
        }
        table {
            width: 100%;
            margin-top: 10px;
        }
        table tr td {
            vertical-align: top;
        }
        .total {
            text-align: right;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center">
            <h2>Nombre de la Empresa</h2>
            <p>Dirección: Lorem Ipsum, 123</p>
            <p>Teléfono: 123-456-789</p>
        </div>

        <div class="separator"></div>

        <p><span class="bold">Ticket N°:</span> {{ $venta->numero_comprobante }}</p>
        <p><span class="bold">Fecha y Hora:</span> {{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d-m-Y H:i') }}</p>
        <p><span class="bold">Cliente:</span> {{ $venta->cliente->persona->razon_social }}</p>

        <div class="separator"></div>

        <table>
            <thead>
                <tr>
                    <td class="bold">Producto</td>
                    <td class="bold" style="text-align:right;">Cant</td>
                    <td class="bold" style="text-align:right;">Subtotal</td>
                </tr>
            </thead>
            <tbody>
                @foreach ($venta->productos as $producto)
                <tr>
                    <td>{{ $producto->nombre }}</td>
                    <td style="text-align:right;">{{ $producto->pivot->cantidad }}</td>
                    <td style="text-align:right;">{{ number_format($producto->pivot->cantidad * $producto->pivot->precio_venta, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="separator"></div>

        <p class="total"><span class="bold">Total:</span> S/. {{ number_format($venta->total, 2) }}</p>

        <div class="separator"></div>

        <p class="text-center">¡Gracias por su compra!</p>
    </div>
</body>
</html>
